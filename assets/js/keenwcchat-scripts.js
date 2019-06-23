(function( $ ) {
	'use strict';

	$( window ).load(function() {

		$(".keenwcchat-textarea").keypress(function (e) {
			if(e.which == 13 && !e.shiftKey) {
				e.preventDefault();       
				$(this).siblings("keenwcchat-send").trigger('click');
				return false;
			}
		});

		$('.keenwcchat-send').on('click', function(e){
			e.preventDefault();
			var messageBox = $(this).siblings('textarea'),
				messageTxt	   = messageBox.val();
			console.log('messageBox', messageBox.val());
			// reset the textarea field
			$(messageBox).val('');
			$.ajax({
				type: 'post',
				url: keenwcchat.ajax,
				data: {
				  action: 'keenwcchat_push_message',
				  message: messageTxt,
				  orderId: keenwcchat.orderId,
				},
				beforeSend: function (resp) {
				  console.log("sending ", messageTxt, keenwcchat.orderId)
				},
				success: function (resp) {
					console.log(resp);
					// showChat(resp);
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
		var displayed = $('#display-chat li').length;
		console.log(displayed);
		$.ajax({
			type: 'post',
			url: keenwcchat.ajax,
			data: {
			  action: 'keenwcchat_load_chat',
			  orderId: keenwcchat.orderId,
			  displayed: displayed,
			},
			beforeSend: function (resp) {
			  console.log("load chat ", keenwcchat.orderId, displayed)
			},
			success: function (resp) {
				console.log(resp);
				showChat(resp);
			},
		})
	}

	// refresh chat every seconds
	setInterval(function(){
		console.log('requesting chat history')
		loadChat();
	}, 5000);

	loadChat();
		
});


})( jQuery );
