(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$( window ).load(function() {
		$('#keenwcchat-message').on('submit', function(e){
			e.preventDefault();
			var messageBox = $(this).find('textarea'),
				messageTxt	   = messageBox.val();
			console.log('messageBox', messageBox.val());
			$(this).trigger('reset');
			$.ajax({
				type: 'post',
				url: woocommerce_params.ajax_url,
				data: {
				  action: 'keenwcchat_push_message',
				  message: messageTxt,
				  orderId: keenwcchat.orderId,
				},
				beforeSend: function (resp) {
				  console.log("sending ", messageTxt, keenwcchat.orderId)
				},
				success: function (resp) {
					showChat(resp.chat);
				},
			})
		});

	// load the messages to show
	function showChat(history){
		var viewMessage = '';
		history.forEach(chat => {
			viewMessage += '<li>' + chat.text + '</li>';
		});
		$('#display-chat').append($(viewMessage).wrap('<ul></ul>'));
	}

	// request for chat history
	function loadChat(){
		$.ajax({
			type: 'post',
			url: woocommerce_params.ajax_url,
			data: {
			  action: 'keenwcchat_load_chat',
			  orderId: keenwcchat.orderId,
			},
			beforeSend: function (resp) {
			  console.log("load chat ", keenwcchat.orderId)
			},
			success: function (resp) {
				showChat(resp.chat);
			},
		})
	}

	// refresh chat every seconds
	// setInterval(function(){
	// 	console.log('requesting chat history')
	// 	loadChat();
	// }, 1000);

	loadChat();

			
});


})( jQuery );
