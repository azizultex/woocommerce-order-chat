var frame;
(function( $ ) {
   'use strict';
    
	$( window ).load(function() {

		var chatBox = $('#display-chat'),
			textArea = $('.keenwcchat-textarea'),
			uploadImage = $("#upload_image"),
			attachmentID = $("#attachment"),
			typeStatus = $('.typing-status'),
			sendBtn = $('.keenwcchat-send'),
			firstLoad = true;

		// emoji one picker
		textArea.emojionePicker({
			type: 'unicode',
		});

		// send message on enter press
		textArea.keypress(function (e) {
			if(e.which == 13 && !e.shiftKey) {
				e.preventDefault();
				sendBtn.trigger('click');
				// console.log('send triggerd')
				return false;
			}
		});


		// upload media button trigger
		uploadImage.on("click",function(){
		   
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
			attachmentID.val(JSON.stringify(attachmentData));
			uploadImage.after('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
			// submit on media file selected? 
			// $( ".keenwcchat-send" ).trigger( "click" );
		 }); 

		  frame.open();
		  return false;
		   
		});

		// send message on send button click
		sendBtn.on('click', function(e){
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
				//   console.log("sending ", messageData)
				},
				success: function (resp) {
					// console.log(resp);
					showChat(resp);
					chatBox.scrollTop(chatBox[0].scrollHeight);
				},
			})
		});

		// load the messages to show
		function showChat(history){
			var displayed = $('#display-chat li').length;
			var viewMessage = '', threadUser, history = history || [];
			history.forEach(chat => {
				var sent_replies = keenwcchat.user == chat.user ? 'sent' : 'replies';
				var image_url = keenwcchat.user == chat.user ? keenwcchat.user_img : keenwcchat.seller_img;
				var image = threadUser !== chat.user ? '<img src="'+ image_url +'" alt></img>' : '';
				var attachment = chat.attachment ? JSON.parse(chat.attachment) : '';
				attachment = attachment ? '<a href="' + attachment.url + '" target="__blank">'+ attachment.filename +'</a>': '';
				viewMessage += '<li class="'+ sent_replies +'">'+image+'<p>'+ chat.text + attachment + '</p><p class="chat_time">'+ unixToJsTime(chat.time) +'</p></li>';
				// console.log(threadUser, chat.user);
				// store previous chat user
				threadUser = chat.user;
			});
			if( displayed === 0 ){
				chatBox.append('<ul>' + viewMessage + '</ul>');
			} else {
				$('#display-chat ul').append(viewMessage);
			}
		}

		// unix timestamp to JavaScript
		function unixToJsTime(ut){
			var months_arr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
				phpToJsTime = ut*1000,
				dateObj = new Date(phpToJsTime),
				timeDiff = new Date((+new Date - phpToJsTime) / 1000),
				minute = 60,
				hour = minute * 60,
				day = hour * 24,
				month = day * 30,
				year = month * 12,
				getYear = dateObj.getFullYear(),
				getMonth = months_arr[dateObj.getMonth()],
				getDay = dateObj.getDate(),
				getHours = dateObj.getHours(), 
				getMinutes = dateObj.getMinutes(),
				ampm = getHours >= 12 ? 'PM' : 'AM',
				hours = getHours % 12,
				hours = hours ? hours : 12, // the hour '0' should be '12'
				keenwcchatFormat = hours.toString().padStart(2, '0') + ':' + getMinutes.toString().padStart(2, '0') + ' ' + ampm;

				// return time conditionally
				if(timeDiff < 30 ){
					return 'now';
				} else if( timeDiff < minute ){
					return timeDiff + ' seconds ago';
				} else if( timeDiff < 2 * minute ){
					return 'a minutes ago';
				} else if( timeDiff < hour ){
					return Math.floor(timeDiff / minute) + ' minutes ago.';
				} else if( Math.floor(timeDiff / hour) == 1 ){
					return '1 hour ago.';
				} else if ( timeDiff < day ) {
					return Math.floor(timeDiff / hour) + ' hours ago.';
				} else if ( timeDiff < 2 * day ) {
					return 'yesterday';
				} else if( timeDiff < year ) {
					return getMonth + ' ' + getDay + ' at ' + keenwcchatFormat;
				} else {
					return getMonth + ' ' + getDay + ', ' + getYear + ' at ' + keenwcchatFormat;
				}
		}

		// request for chat history
		function loadChat(){
			var displayed = $('#display-chat li').length;
			$.ajax({
				type: 'post',
				url: keenwcchat.ajax,
				data: {
				action: 'keenwcchat_load_chat',
				orderId: keenwcchat.orderId,
				displayed: displayed,
				},
				beforeSend: function (resp) {
					// console.log("load chat ", keenwcchat.orderId, displayed)
				},
				success: function (resp) {
					// console.log('success', resp);
					showChat(resp);
					// scroll down to last chat for first load only
					if(firstLoad){
						chatBox.scrollTop(chatBox[0].scrollHeight);
						firstLoad = false;
					}
				},
			})
		}

		textArea.on('focus', function(e){
			$.post(keenwcchat.ajax, { action: 'keenwcchat_typing' }, function(resp){
				// console.log(resp);
			});
		});

		textArea.on('blur', function(e){
			$.post(keenwcchat.ajax, { action: 'keenwcchat_not_typing' }, function(resp){
				// console.log(resp);
			});
		});

		function getTypingStatus(){
			$.post(keenwcchat.ajax, { action: 'get_typing_status', 'chatingWith': keenwcchat.chatingWith }, function(resp){
				if(resp.typing){
					typeStatus.text('Typing...')
				} else {
					typeStatus.text('');
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
