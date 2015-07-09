<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp-question');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '5rA%A[lG/X15{zzb([63d(J)}(|k2-ve&Ak09JCb`#]~NnrGkltML#6%XqxEj33G');
define('SECURE_AUTH_KEY',  '4gQQ=+RB_PfwpO~aR-|}A~p:0M$D_zT%g3)&3qM[{z~A.vMK>Q|@1kz@=C^9*niV');
define('LOGGED_IN_KEY',    'k>&<G~9<05p8A5CnvZa{1vAe5cTv(GcNZ:C*)pQd6.jmgTU5|=U>cao<|]`Yj,|A');
define('NONCE_KEY',        '#0J_@/U?d.j W^_5eTFp[J|&U6|F57jw@BoTO^r3$AcR,<+~L!UEsnIc3^mo1Sgf');
define('AUTH_SALT',        'm6{?h(97{RtwR;Ga1c8xf8bhi06Z,Pbk/)04FVgR5o$+-2qm3|{2Y2(B:TS:e3w/');
define('SECURE_AUTH_SALT', 'ZJu@#x*KA8y_HOt.B{HRZ4B%75N`eY5<S+^WUE+t@Hxk9|M5xPc+d2j*3{1aP M0');
define('LOGGED_IN_SALT',   'Pbg74UJiG,3Y@&GbJgtQNRkp-9c-KRBvgz$b[y+-.G4~<-+Z1F1`3@ndTH~tc .l');
define('NONCE_SALT',       'jJ3V&|e %miP8+kV0QkRxX}I@r7qXhS*Z*n5-x:.lABrco9m@Z~bV:XK`0p)C=VF');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
