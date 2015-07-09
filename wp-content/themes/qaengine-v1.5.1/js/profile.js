(function(Views, Models, $, Backbone) {

	Views.UserProfile = Backbone.View.extend({
		el: 'body',

		events: {
			'click a.show-edit-form': 'openEditProfileForm',
			'click .inbox': 'openContactModal',
		},

		initialize: function() {
			this.user    = new Models.User(currentUser);
			this.blockUi = new AE.Views.BlockUi();
		},
		openEditProfileForm: function(event) {
			event.preventDefault();
			if( typeof this.modalEditProfile == "undefined"){
				this.modalEditProfile = new Views.EditProfileModal({
					el: $("#edit_profile")
				});
			}
			this.modalEditProfile.openModal();
		},
		openContactModal: function(event) {
			event.preventDefault();
			if( typeof this.modalContact == "undefined"){
				this.modalContact = new Views.ContactModal({
					el: $("#contactFormModal")
				});
			}
			this.modalContact.openModal();
		}
	});

})(QAEngine.Views, QAEngine.Models, jQuery, Backbone);