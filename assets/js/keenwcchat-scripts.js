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
			threadUser = null; // need to track message thread to hide avatar and name

		// Your web app's Firebase configuration
		var firebaseConfig = {
			apiKey: keenwcchat.firebase.api_key,
			authDomain: keenwcchat.firebase.projectId + '.firebaseapp.com',
			databaseURL: "https://" + keenwcchat.firebase.projectId +".firebaseio.com",
			projectId: keenwcchat.firebase.projectId,
			storageBucket: "",
			messagingSenderId: "",
			appId: ""
		};
		// Initialize Firebase
		firebase.initializeApp(firebaseConfig);

		//create firebase database reference
		var dbRef = firebase.database();
		 // create firebase db with the order id
		var chatsRef = dbRef.ref('chats-' + keenwcchat.orderId);

		// emoji one picker
		textArea.emojionePicker({
			type: 'unicode',
		});

		// send message on enter press
		textArea.keypress(function (e) {
			if(e.which == 13 && !e.shiftKey) {
				e.preventDefault();
				sendBtn.trigger('click');
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
				var attachmentData = { id: attachment.id, url: attachment.url, filename: attachment.filename}
				attachmentID.val(JSON.stringify(attachmentData));
				uploadImage.after('<a class="attachedLink" href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
			}); 

		  frame.open();
		  return false;
		   
		});

		// send message on send button click
		sendBtn.on('click', function(e){
			e.preventDefault();
			var attachment	= attachmentID.val(),
				parsedAttach = attachment ? JSON.parse(attachment) : {},
				messageData =	{
					user: keenwcchat.user,
					message: textArea.val(),
					time: new Date().getTime(),
				};

				// return if nothing to push
				if(!messageData.message.trim() && !attachment ){
					return;
				}
				// add media if available
				if(parsedAttach.url){
					messageData.attachment = parsedAttach;
				}

				console.log('messageData', messageData);
				
				// reset the textarea field
				textArea.val('');
				attachmentID.val('');
				$('.attachedLink').remove();

				// push the data to firebase
				chatsRef.push(messageData);
		});

		//load older conatcts as well as any newly added one...
		chatsRef.on("child_added", function(snap) {
			chatBox.append(showChat(snap.val()));
			chatBox.scrollTop(chatBox[0].scrollHeight);
		});

		// load the messages to show
		function showChat(chat){
				var html = '',
					sent_replies = keenwcchat.user === chat.user ? 'sent' : 'replies',
					user = chat.user == keenwcchat.customer.id ? keenwcchat.customer : keenwcchat.seller,
					userName = threadUser !== chat.user ? user.name : '',
					attachment = chat.attachment ? chat.attachment : '',
					attachment = attachment ? '<a href="' + attachment.url + '" target="__blank">'+ attachment.filename +'</a>': '',
					userName = userName ? '<b>' + userName + '</b>' : '';

				html += '<div class="' + sent_replies + '">';
					if( threadUser !== chat.user ){
						html += '<div class="avatar"><img src="'+ user.avatar +'"></div>';
					}
					html += '<div class="message">';
						html += '<p class="name_time">' + userName + '<span> ' + unixToJsTime(chat.time) + '</span></p>';
					html += '<p>' + chat.message + '</p>';
					html += '<p>' + attachment + '</p>';
					html += '</div>';
				html += '</div>';

				threadUser = chat.user; // need to track message thread to hide avatar and name
				return html;
		}

		// unix timestamp to JavaScript
		function unixToJsTime(ut){
			var months_arr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
				// phpToJsTime = ut*1000,
				dateObj = new Date(ut),
				timeDiff = new Date((+new Date - ut) / 1000),
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

		textArea.on('focus', function(e){
			console.log('typing')
			$.post(keenwcchat.ajax, { action: 'keenwcchat_typing' }, function(resp){
				console.log(resp);
			});
		});

		textArea.on('blur', function(e){
			$.post(keenwcchat.ajax, { action: 'keenwcchat_not_typing' }, function(resp){
				console.log(resp);
			});
		});

		function getTypingStatus(){
			$.post(keenwcchat.ajax, { action: 'get_typing_status', 'chatingWith': keenwcchat.chatingWith }, function(resp){
				console.log('getting typing status')
				if(resp.typing){
					typeStatus.text('Typing...')
				} else {
					typeStatus.text('');
				}
			});
		}

		// keep updating typing status
		setInterval(function(){
			// console.log('requesting typing status')
			getTypingStatus();
		}, 2000);
			
	});
	
})( jQuery );
