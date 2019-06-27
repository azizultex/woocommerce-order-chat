(function( $ ) {
	'use strict';

	$( window ).load(function() {

		$(".keenwcchat-textarea").keypress(function (e) {
			if(e.which == 13 && !e.shiftKey) {
				e.preventDefault();       
				$(this).siblings(".keenwcchat-send").trigger('click');
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
					showChat(resp);
				},
			})
		});

	// load the messages to show
	function showChat(history){
		var displayed = $('#display-chat li').length;
		var viewMessage = '', threadUser;
		history.forEach(chat => {
			var sent_replies = keenwcchat.user == chat.user ? 'sent' : 'replies';
			var image_url = keenwcchat.user == chat.user ? keenwcchat.user_img : keenwcchat.seller_img;
			var image = threadUser !== chat.user ? '<img src="'+ image_url +'" alt></img>' : '';
			viewMessage += '<li class="'+ sent_replies +'">'+ image +'<p>' + chat.text + '</p></li>';
			console.log(threadUser, chat.user);
			// store previous chat user
			threadUser = chat.user;
		});
		if( displayed === 0 ){
			$('#display-chat').append('<ul>' + viewMessage + '</ul>');
		} else {
			$('#display-chat ul').append(viewMessage);
		}
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

	$('.keenwcchat-textarea').on('focus', function(e){
		$.post(keenwcchat.ajax, { action: 'keenwcchat_typing' }, function(resp){
			console.log(resp);
		});
	});

	$('.keenwcchat-textarea').on('blur', function(e){
		$.post(keenwcchat.ajax, { action: 'keenwcchat_not_typing' }, function(resp){
			console.log(resp);
		});
	});

	function getTypingStatus(){
		$.post(keenwcchat.ajax, { action: 'get_typing_status' }, function(resp){
			if(resp.typing){
				$('.typing-status').text('Typing...')
			} else {
				$('.typing-status').text('');
			}
		});
	}

	// refresh chat every seconds
	setInterval(function(){
		console.log('requesting chat history')
		loadChat();
	}, 5000);

	// keep updating typing status
	setInterval(function(){
		console.log('requesting typing status')
		getTypingStatus();
	}, 2000);

	// first load message history
	loadChat();
		
});


})( jQuery );
