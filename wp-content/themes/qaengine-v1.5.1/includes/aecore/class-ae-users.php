<?php
// if(!defined('ET_DOMAIN')) {
// 	wp_die('API NOT SUPPORT');
//}
/**
 * Class AE users, control all action with user data
 * @author Dakachi
 * @version 1.0
 * @package AE
 * @since 1.0
*/
class AE_Users {
	static $instance;

	/**
	 * return class $instance
	*/
	public static function get_instance() {
		if(self::$instance == null) {

			self::$instance	=	new AE_Users ();
		}
		return self::$instance;
	}

	/**
	 * contruct a object user with meta data
	 * @param array $meta_data all user meta data you want to control
	 * @author Dakachi
	 * @since 1.0
	*/
	public function __construct($meta_data = array()){
		$defaults			=	array('location', 'address', 'avatar', 'post_count', 'comment_count' );
		$this->meta_data	=	wp_parse_args( $meta_data, $defaults );
	}
	/**
	 * convert userdata to an object
	 * @param object $user
	 * @return user object after convert
	 * 		   - wp_error object if user invalid
	 * @author Dakachi
	 * @since 1.0
	*/
	public function convert( $user ){

		if( !$user->ID ) return new WP_Error('ae_invalid_user_data' , __("Input invalid", ET_DOMAIN) );

		$result = isset($user->data) ? $user->data : $user ;

		foreach ($this->meta_data as $key) {
			$result->$key = get_user_meta( $result->ID, $key, true );
		}

		$result->avatar		= get_avatar( $user->ID );
		$result->join_date	= sprintf(__("Join on %s", ET_DOMAIN), (string)date(get_option('date_format'), strtotime($user->user_registered) )) ;

		/**
		 * get user role
		*/
		if(current_user_can( 'edit_users' )) {
			$user_role	=	$user->roles;
			$result->role		=	array_pop($user_role);
		}
		/**
		 * get all user meta data
		*/
		$author_metas		= array('display_name', 'first_name', 'last_name' , 'description', 'user_url');
		foreach ($author_metas as $key => $author_meta) {
			$result->$author_meta	=	get_the_author_meta( $author_meta, $result->ID );
		}

		/**
		 * return post count
		*/
		$result->post_count	=	count_user_posts( $result->ID );
		/**
		 * return comment count
		*/
		$result->comment_count	=	ae_comment_count( $result->user_email );

		$result->id	=	$result->ID;
		unset($result->user_pass);

		return apply_filters( 'ae_convert_user',  $result );
	}

	/**
	 * insert userdata and user metadata to an database
	 # used wp_insert_user
	 # used update_user_meta
	 # user AE_Users function convert
	 * @param 	array $user data
	 			# wordpress user fields data
	 			# user custom meta data
	 * @return 	user object after insert
	 	 		# wp_error object if user data invalid
	 * @author Dakachi
	 * @since 1.0
	*/
	public function insert( $user_data ){

		if( !$user_data['user_login'] || !preg_match('/^[a-z\d_]{2,20}$/i', $user_data['user_login']) ) {
			return new WP_Error( 'username_invalid', __("Username only lowercase letters (a-z) and numbers are allowed.", ET_DOMAIN) );
		}
        if (!isset($user_data['user_email']) || !$user_data['user_email'] || $user_data['user_email'] == '' || !is_email($user_data['user_email']) ) {
            return new WP_Error('email_invalid', __("Email field is invalid.", ET_DOMAIN));
        }
        if (!isset($user_data['user_pass']) || !$user_data['user_pass'] || $user_data['user_pass'] == '' ) {
            return new WP_Error('pass_invalid', __("Password field is required.", ET_DOMAIN));
        }
        if (isset($user_data['repeat_pass']) && $user_data['user_pass'] != $user_data['repeat_pass']) {
            return new WP_Error('pass_invalid', __("Repeat Passwords mismatch.", ET_DOMAIN));
        }
        $user_data = apply_filters( 'ae_pre_insert_user', $user_data );
        if(!$user_data || is_wp_error( $user_data )) {
            return $user_data;
        }

        //check role for users
        if( !isset($user_data['role']) ) {
            $user_data['role'] = 'author';
        }

        /**
         * insert user by wp_insert_user
         */
        $result = wp_insert_user($user_data);

        if ($result != false && !is_wp_error($result)) {

            /**
             * update user meta data
             */
            foreach ($this->meta_data as $key => $value) {

                // update if meta data exist
                if (isset($user_data[$value])) update_user_meta($result, $value, $user_data[$value]);
            }

            // people can modify here
            do_action('ae_insert_user', $result);

            /**
             * add ID to user data and return object
             */
            $user_data['ID'] = $result;

            // sign on user
            // if( !get_option( 'user_confirm' ) && !is_user_logged_in() ) {
            //  return $this->login($user_data);
            // }else {
            //  return $this->convert(get_userdata($user_data['ID']));
            // }
            $result = $this->login($user_data);
        }
        //set return message
        //if(isset($result->msg)){
            $result->msg = !ae_get_option('user_confirm') ? __("You have registered successfully!", ET_DOMAIN) : __("You have registered successfully. Please check your mailbox to activate your account.", ET_DOMAIN);
        //}

        return apply_filters( 'ae_after_insert_user', $result);
    }

    /**
     * update userdata and user metadata to an database
     # used wp_update_user , wp_authenticate, email_exists ,get_userdata
     # used update_user_meta
     # used AE_Users function convert
     * @param   array $user data
     # wordpress user fields data
     # user custom meta data
     * @return  user object after insert
     # wp_error object if user data invalid
     * @author Dakachi
     * @since 1.0
     */
    public function update($user_data) {
        global $current_user, $user_ID;

        /**
         * prevent user edit other user profile
         */
        if (!current_user_can('edit_users') && $user_data['ID'] != $user_ID) {
            return new WP_Error('denied', __("Permission Denied!", ET_DOMAIN));
        }

        /**
         * check user password if have new password update
         */
        if (isset($user_data['new_password']) && !empty($user_data['new_password'])) {
            $validate = $this->check_password($user_data);
            if($validate){
                $user_data['user_pass'] = $user_data['new_password'];
            } else {
                return new WP_Error('wrong_pass', __("Old password does not match!", ET_DOMAIN));
            }

            if($user_data['new_password'] !== $user_data['renew_password']) {
                return new WP_Error('pass_mismatch', __("Retype password is not equal.", ET_DOMAIN));
            }
        }

        if (isset($user_data['user_email'])) {
            $email = $user_data['user_email'];

            /**
             * current user also update his email
             */
            if ($user_ID == $user_data['ID'] && $email != $current_user->user_email) {
                if (email_exists($email)) {
                    return new WP_Error('email_existed', __("This email is already used. Please enter a new email.", ET_DOMAIN));
                }
            }
        }

        // don't allow upgrade from common user to admin
        if (!current_user_can('edit_users')) {
            unset($user_data['role']);
            unset($user_data['user_login']);
        }

        /**
         * insert user
         */
        $result = wp_update_user($user_data);

        if ($result != false && !is_wp_error($result)) {

            /**
             * update user meta data
             */
            foreach ($this->meta_data as $key => $value) {

                // update if meta data exist
                if (isset($user_data[$value])) update_user_meta($result, $value, $user_data[$value]);
            }

            // hook to add custom
            do_action('ae_update_user', $result, $user_data);

            /**
             * get user data and return a full profile
             */
            $result = $this->convert(get_userdata($result));
        }
        if (isset($user_data['do'])) {
            switch ($user_data['do']) {
                case 'profile':
                    $result->msg = __("Your profile has been saved successfully!", ET_DOMAIN);
                    break;
                case 'changepass':
                    $result->msg = __("Your password has been changed successfully!", ET_DOMAIN);
                    break;
                default:
                    $result->msg = __("User's data update successfully!", ET_DOMAIN);
                    break;
            }
        } else {
            $result->msg = __("User's data update successfully!", ET_DOMAIN);
        }
        return $result;
    }

    /**
     * login user into website
     # used wp_signon
     # used AE_Users function convert
     * @param   array $user data
     # wordpress user fields data
     # user custom meta data
     * @return  user object after insert
     # wp_error object if user data invalid
     * @author Dakachi
     * @since 1.0
     */
    public function login($user_data) {
        global $current_user;

        // echo 'login';
        // check users if he is member of this blog
        $user = get_user_by('login', $user_data['user_login']);
        // if login by username failed check by email
        if (is_wp_error($user) || !$user) {
            $user = get_user_by('email', $user_data['user_login']);
        }
        /**
         * check user infomation
        */
        if (!$user) {
            return new WP_Error('login_failed', __("The login information you entered were incorrect. Please try again!", ET_DOMAIN));
        }
    }

	/**
	 * check user password, compare it with retype pass, validate old pass
	 * @return 	object WP_Error
	 * 			bool true
	 * @author	Dakachi
	 * @since	1.0
	*/
	public function check_password () {
		global $current_user;

		if( $user_data['ID'] !== $current_user->ID && !current_user_can( 'remove_users' ) )  {
			return new WP_Error ('ae_permission_denied', __("You cannot change other user password", ET_DOMAIN) );
		}

		if($data['renew_password'] != $data['user_pass']) // password missmatch
			return new WP_Error ('ae_pass_mismatch', __("Retype password is not equal.", ET_DOMAIN));

		$old_pass 		= $data['old_password'];
		$authentication	= wp_authenticate( $current_user->user_login, $old_pass );
		if(is_wp_error($aut)){ // check authentication
			// unset($data['renew_password']);
			// unset($data['old_password']);
			return new WP_Error ('false', __("The password entered do not match.", ET_DOMAIN));
		}

		return true;
	}

	/**
	 * convert userdata to an object , use function convert
	 * @param object $user
	 * @return array of objects user
	 * @author Dakachi
	 * @since 1.0
	*/
	public function fetch($args) {

		if (  isset($args['search']) && '' !== $args['search']  ) {
			$args['search'] = '*' . $args['search'] . '*';
			$args['search_columns'] = array('ID', 'user_nicename', 'user_login');
		}

		$users_query	=	new WP_User_Query( $args );
		$users			=	$users_query->results;

		$user_data		=	array();

		foreach ($users as $key => $user) {
			$convert		=	$this->convert( $user );
			if(!is_wp_error( $convert )) {
				$user_data[]	=	$this->convert( $user );
			}
		}
		return array('pages' => ceil($users_query->total_users/$args['number']), 'data' =>  $user_data ) ;
	}

	/**
	 * sync user data with server
	 * @param array $request The user data array will be insert to database
	 * @return object user object after converted
	 * @since 1.0
	 * @author Dakachi
	*/
	public function sync($request) {
		extract( $request );
		unset($request['method']);

		/**
		 * check request method to set the action
		*/
		switch ( $method ) {
			case 'create':
				return new WP_Error('invalid_permission', __("You don't have permission to create new user!", ET_DOMAIN) );
				break;
			case 'update':
				$result	=	$this->update( $request );
				break;
			case 'remove':
				$result	=	$this->delete( $request['ID'] );
				break;
			case 'read':
				$result	=	$this->get( $request['ID'] );
				break;
			default :
				return new WP_Error('invalid_method', __("Invalid method", ET_DOMAIN) );
		}

		/**
		 * return object user
		*/
		return $result;
	}
}

/**
 * class acting with all action for user
*/
class AE_UserAction extends AE_Base {

	/**
	 * property AE_Users
	*/
	protected $user;

	public function __construct(AE_Users $user) {
		$this->user	=	$user;
		$this->add_ajax('ae-fetch-users', 'fetch');
		$this->add_ajax('ae-sync-user', 'sync');
	}
	/**
	 * ajax fetch users sync
	*/
	function fetch() {

		$post_per_page	=	get_option( 'posts_per_page' );
		$request		=	$_REQUEST;

		$offset			=	($request['paged'])*$post_per_page;

		$args			=	array( 'offset' => $offset , 'number' => $post_per_page );
		$args			=	wp_parse_args( $args, $request );

		$users			=	$this->user->fetch( $args );

		$response	=	array( 'success' => true,
								'data' 	=> $users['data'],
								'pages'	=> $users['pages'],
								'paged' => $request['paged']+1 ,
								'msg' => __("Get users successfull", ET_DOMAIN)
							);
		if(empty($users['data'])) {
			$response['msg']	=	__("No user found by your query", ET_DOMAIN);
		}

		wp_send_json( $response );
	}

	/**
	 * callback for ajax ae-sync-user action
	*/
	function sync() {
		$request	=	$_REQUEST;
		$result	=	$this->user->sync($request);
		if($result && !is_wp_error( $result ) ) {
			$response	=	array('success' => true, 'data' => $result, 'msg' => __("Update user successful!", ET_DOMAIN))	;
		}else {
			$response	=	array('success' => false,  'msg' => $result->get_error_message() ) ;
		}

		wp_send_json( $response );
	}

}