var frame;
(function( $ ) {
   'use strict';
    
	$( window ).load(function() {

		// emoji one picker
		$('.keenwcchat-textarea' ).emojionePicker({
			type: 'unicode',
		});

		// send message on enter press
		$(".keenwcchat-textarea").keypress(function (e) {
			if(e.which == 13 && !e.shiftKey) {
				e.preventDefault();       
				$(this).siblings(".keenwcchat-send").trigger('click');
				return false;
			}
		});


		// upload media button trigger
		$("button#upload_image").on("click",function(){
		   
			if(frame){
				frame.open();
				return false;
			}
           
		   frame = wp.media({
			 title:"Upload image",
			 button:{
				 text:"select image"
			 },
			 multiple:false
		   });

		    frame.on("select" , function(){
			var attachment = frame.state().get("selection").first().toJSON();
			// $("#image_id").val(attachment.id)
			var attachmentData = { id: attachment.id, url: attachment.url, filename: attachment.filename}
			$("#attachment").val(JSON.stringify(attachmentData));
			$("#upload_image").after('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
			// submit on media file selected? 
			// $( ".keenwcchat-send" ).trigger( "click" );
		 }); 

		  frame.open();
		  return false;
		   
		});

		// send message on send button click
		$('.keenwcchat-send').on('click', function(e){
			e.preventDefault();
			var messageBox	=	$(this).siblings().find('textarea') || $(this).siblings('textarea'),
				attachment	=	$(this).siblings('input[name=attachment]').val(),
				messageData =	{
					action: 'keenwcchat_push_message',
					message: messageBox.val(),
					attachment: attachment,
					orderId: keenwcchat.orderId,
				};
			
			console.log('messageData', messageData);

			// reset the textarea field
			$(messageBox).val('');

			// perform ajax for message update
			$.ajax({
				type: 'post',
				url: keenwcchat.ajax,
				data: messageData,
				beforeSend: function (resp) {
				  console.log("sending ", messageData)
				},
				success: function (resp) {
					// console.log(resp);
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
				var attachment = chat.attachment ? JSON.parse(chat.attachment) : '';
				attachment = attachment ? '<a href="' + attachment.url + '" target="__blank">'+ attachment.filename +'</a>': '';
				viewMessage += '<li class="'+ sent_replies +'">'+ image +'<p>' + chat.text + attachment + '</p></li>';
				// console.log(threadUser, chat.user);
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
					console.log('success', resp);
					showChat(resp);
				},
			})
		}

		$('.keenwcchat-textarea').on('focus', function(e){
			$.post(keenwcchat.ajax, { action: 'keenwcchat_typing' }, function(resp){
				// console.log(resp);
			});
		});

		$('.keenwcchat-textarea').on('blur', function(e){
			$.post(keenwcchat.ajax, { action: 'keenwcchat_not_typing' }, function(resp){
				// console.log(resp);
			});
		});

		function getTypingStatus(){
			$.post(keenwcchat.ajax, { action: 'get_typing_status', 'chatingWith': keenwcchat.chatingWith }, function(resp){
				if(resp.typing){
					$('.typing-status').text('Typing...')
				} else {
					$('.typing-status').text('');
				}
			});
		}

		// refresh chat every seconds
		setInterval(function(){
			// console.log('requesting chat history')
			loadChat();
		}, 5000);

		// keep updating typing status
		setInterval(function(){
			// console.log('requesting typing status')
			getTypingStatus();
		}, 2000);

		// first load message history
		loadChat();
			
	});


})( jQuery );
