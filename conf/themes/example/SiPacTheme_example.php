<?php

class SiPacTheme_example extends SiPacTheme
{
	public function get_settings()
	{
		$settings['smiley_height'] = 30;
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($nickname, $user_num, $smileys, $settings)
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		return "
			<div class='chat_main'>
					<nav class='chat_channels_nav'>
						<ul class='chat_channels_ul'></ul>
						<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'>+</a></span>
					</nav>
						<div class='chat_conversation'></div>
						<div class='chat_userlist'></div>
						<div class='chat_settings'>$settings</div>
						
						<div class='chat_user_input'>
							<div class='chat_notice_msg'></div>
							<div>$smileys</div>
							<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
							<button class='chat_send_button'><||send-button-text||></button>
							<button onclick='$theme.test(\"blubb\");'>layout function test</button>
						</div>
							
			</div><!-- end: chat_main-class -->
		";
	}
	
	public function get_js_functions()
	{
		$functions['init'] = '
		function ()
		{
			this.old_user_status = new Array();
		}
		';
		$functions['layout_user_writing_status'] = '
		function (status, username, user_id)
		{
			if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.texts["writing-status"] + "]")
				this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
		
			if (status == 1)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.texts["writing-status"] + "]";
			else if (this.old_user_status[username] != undefined)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
		}
		';
		$functions['test'] = '
		function (var1)
		{
			alert(var1);
		}
		';
		return $functions;
	}
	/* optional methods */
	/*
	public function get_userlist_entry($nickname, $status, $info, $color, $id)
	{
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		return 
		"
		<div class='chat_user' id='$id'>
		<div class='chat_user_name' $style>$nickname<span class='chat_user_status'>[$status]</span></div>
		
		<div class='chat_user_bottom'>
		<div class='chat_user_info'>$info</div>
		</div>
		</div> 
		";
	}
	
	public function get_user_info($info_head, $info_text)
	{
		return "<div><span>$info_head:</span><span style='float: right'>$info_text</span></div>";
	}
	
	public function get_message_entry($message, $nickname, $type, $color, $time)
	{
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		if ($type == "own" OR $type == "others")
		{
			return
			"
			<div class='chat_entry_$type'>
			<span class='chat_entry_user' $style>$nickname</span>:
			<span class='chat_entry_message'>$message</span>
			<span class='chat_entry_date'>$time</span>
			</div>  
			";
		}
		else if ($type == "notify")
		{
			return 
			"
			<div class='chat_entry_notify'>
			<span class='chat_entry_message'>$message</span>
			<span class='chat_entry_date'>$time</span>
			</div>
			";
		}
	}
	
	public function get_nickname($nickname)
	{
		return "<span class='chat_entry_user'>$nickname</span>";
	}
	
	public function get_channel_tab($channel, $id, $change_function, $close_function)
	{
		return 
		"
		<li id='$id'>
		<span class='chat_channel_span'>
		<a class='chat_channel' href='javascript:void(0);' onclick='$change_function'>$channel</a><a href='javascript:void(0);' onclick='$close_function' class='chat_channel_close'>X</a>
		</span>
		</li>
		";
	}
	public function get_post_date($date, $time_format, $date_format)
	{
		$date_text = date($time_format, $date);
				
		if (date("d.m.Y", $date) != date("d.m.Y", time()))
			$date_text = date($date_format, $date). " " . $date_text;
					
		return $date_text;
	}
	public function get_js_settings()
	{
		return "
		<input checked='checked' class='chat_notification_checkbox' type ='checkbox' onclick='if ($js.notifications_enabled == true) { $js.disable_notifications(); } else { $js.enable_notifications();} '><||enable-desktop-notifications-text||>
		<br><input type ='checkbox' class='chat_autoscroll_checkbox' checked='checked' onclick='if ($js.autoscroll_enabled == true) { $js.disable_autoscroll() } else { $js.enable_autoscroll() } '><||enable-autoscroll-text||>
		<br><input type ='checkbox' class='chat_sound_checkbox' checked='checked' onclick='if ($js.sound_enabled == true) { $js.disable_sound() } else { $js.enable_sound() } '><||enable-sound-text||>
		<br><input type ='checkbox' class='chat_invite_checkbox' checked='checked' onclick='if ($js.invite_enabled == true) { $js.disable_invite() } else { $js.enable_invite() } '><||enable-invite-text||>
		";
	}
	*/
}