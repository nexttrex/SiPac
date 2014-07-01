<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013 Jan Houben

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
class SiPac_Layout
{
	private $js_chat;
	private $html_code;
	private $chat;
	
	public $is_mobile = false;
	public $arr;
	public $cache_folder;
	
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	//generate and return the html code for the chat
	public function draw()
	{   
		$this->cache_folder = md5($this->chat->id.$this->chat->html_path.$this->chat->settings->get('theme'));
		
		$GLOBALS['global_chat_num']  = $GLOBALS['global_chat_num'] + 1;
		if ($this->chat->settings->get('use_cache'))
		{
			$cache_folder = dirname(__FILE__) . "/../../../cache/".$this->cache_folder."/";	
			if ($this->is_mobile)
				$cache_folder = $cache_folder."mobile/";
				
			if (is_dir($cache_folder) == false)
			{
				mkdir($cache_folder, 0777);
				
				$html_code = $this->chat->ranslate($this->generate_layout_html());
				file_put_contents($cache_folder."layout.html", $html_code);
				file_put_contents($cache_folder."layout.css", $this->generate_layout_css());
			}
			
			return file_get_contents($cache_folder."layout.html").$this->generate_js();
		}
		else
		{
			$css = $this->generate_layout_css();
			return  $this->chat->language->translate($this->generate_layout_html($css)).$this->generate_js();
		}
	}
	private function generate_layout_html($css_code=false)
	{
		//save the html code of the layout
		$html_code = $this->arr['html'];
	
		//add the chat id to the div with the class 'chat_main'
		$html_code =str_replace('"chat_main"', '"chat_main" id="' . $this->chat->id . '"', str_replace("'chat_main'", "'chat_main' id='".$this->chat->id."'", $html_code));
	
		//load the chat.js
		$html_code = $html_code."<script class='sipac_script' type='text/javascript' src='".$this->chat->html_path."src/javascript/chat.js'></script>";
		if (empty($css_code))
		{
			$cache_folder = $this->chat->html_path."cache/".$this->cache_folder."/";
			if ($this->is_mobile)
				$cache_folder = $cache_folder."mobile/";
			$html_code = "<link rel='stylesheet' type='text/css' href='".$cache_folder."layout.css'>".$html_code;
		}
		else
		{
			//load the layout css file via javascript
			$html_code = $html_code."
			<script class='sipac_script' type='text/javascript'>
			var style_obj = document.createElement('style');
			var text_obj = document.createTextNode('".trim($css_code)."');
			style_obj.appendChild(text_obj);
			document.getElementById('".$this->chat->id."').appendChild(style_obj);
			</script>";
		}
		$html_code = str_replace("!!NUM!!", $this->chat->chat_num, $html_code);
		$html_code = str_replace("!!USER_NUM!!", "<span class='chat_user_num'>?</span>", $html_code);
		$html_code = str_replace("!!SMILEYS!!", $this->generate_smileys(), $html_code);

		return $html_code;
	}
  
	private function generate_layout_css()
	{
	$css_file = str_replace("\n", " ", file_get_contents($this->arr['path']."/chat.css"));
	$id_css   = "#".$this->chat->id;
	
	$css_files_parts = explode("{", $css_file);
	$new_css_file = str_replace("##CHAT_MAIN##", $id_css, $css_file);
	
	return $new_css_file;
	}
  
	private function generate_js()
	{
		$js_chat = "<script class='sipac_script' type='text/javascript'>
		var chat_text = new Array();";
		foreach ($this->chat->language->text as $key => $text)
		{
			$js_chat = $js_chat."chat_text['$key'] = '".addslashes($text)."';";
		}
	
		$js_chat = $js_chat."var chat_layout = new Array();";
		$js_chat = $js_chat."chat_layout['channel_tab'] = '".addslashes(trim(str_replace("	", " ", str_replace("\n", " ", $this->arr['channel_tab']))))."';";
	
		$js_chat = $js_chat."var chat_channels = new Array(";
		foreach ($this->chat->channel->list as $key => $channel)
		{
			if ($key != 0)
				$js_chat = $js_chat.",";
			$js_chat = $js_chat."\"".$channel['id']."\"";  
		}
		$js_chat = $js_chat.");";
	
		$js_chat = $js_chat."var chat_channel_titles = new Array(";
		foreach ($this->chat->channel->list as $key => $channel)
		{
			if ($key != 0)
				$js_chat = $js_chat.",";
			$js_chat = $js_chat."\"".$channel['title']."\"";  
		}
		$js_chat = $js_chat.");";
	
		/* generate js function arguments, to start the chat*/
		$js_chat =$js_chat. "add_chat('".$this->chat->html_path."','" . $this->chat->settings->get('theme') . "','".$this->chat->id."', '".$this->chat->client_num."', chat_channels, chat_channel_titles, chat_text, chat_layout";
	

	
		$js_chat = $js_chat.");";
		
		//load all javascript functions of the layout
		if (!empty($this->arr['javascript_functions']))
		{
			foreach ($this->arr['javascript_functions'] as $name => $function)
			{
				$js_chat = $js_chat."chat_objects[chat_objects.length-1].$name = $function;";
					if ($name == "layout_init")
						$js_chat = $js_chat."chat_objects[chat_objects.length-1].$name();";
			}
		}
		$js_chat = $js_chat."chat_objects[chat_objects.length-1].init();</script>";
	
		return $js_chat;
	}
	private function generate_smileys()
	{
		$chat_smileys = "";
		if ($this->chat->settings->get('smiley_width') == "!!AUTO!!")
			$width = "";
		else
			$width = " width='" . $this->chat->settings->get('smiley_width') . "px'";
		if ($this->chat->settings->get('smiley_height') == "!!AUTO!!" AND $this->chat->settings->get('smiley_width') == "!!AUTO!!" AND isset($this->arr['default_smiley_height']))
			$height = " height='" .$this->arr['default_smiley_height'] . "'";
		else if ($this->chat->settings->get('smiley_height') == "!!AUTO!!")
			$height = "";
		else
			$height = " width='" . $this->chat->settings->get('smiley_height') . "px'";
		foreach ($this->chat->settings->get('smileys') as $smiley_code => $smiley_url)
		{
			if (strpos($smiley_url, "http://") === false)
				$smiley_url = $this->chat->html_path . "themes/" . $this->chat->settings->get('theme') . "/smileys/" . $smiley_url;
			$smiley_code  = htmlentities($smiley_code);
			$chat_smileys = $chat_smileys . "<span style='margin-right: 3px; cursor: pointer;' onclick='chat_objects[chat_objects_id[\"".$this->chat->id."\"]].add_smiley(\" " . $smiley_code . "\");'><img$width$height src='" . $smiley_url . "' title='" . $smiley_code . "' alt='" . $smiley_code . "'></span>";
		}
		return $chat_smileys;
	}
  
	public  function load()
	{
		$is_mobile = check_mobile();
    
		$layout_php_path = dirname(__FILE__) . "/../../../themes/".$this->chat->settings->get('theme')."_mobile";
     
		if ($is_mobile && is_dir($layout_php_path))
			$this->is_mobile = true;
		else
			$layout_php_path = dirname(__FILE__) . "/../../../themes/".$this->chat->settings->get('theme');
		
		$layout_path = $this->chat->html_path."themes/".$this->chat->settings->get("theme");
		
		if (file_exists($layout_php_path."/layout.php"))
			require($layout_php_path."/layout.php");
		else
			die($layout_php_path."/layout.php not found!");
		
		$layout_array['path'] = $layout_php_path;
		$layout_array['html'] = $chat_layout;
		$layout_array['user_html'] = $chat_layout_user_entry;
		$layout_array['default_smiley_height'] = $default_smiley_height;
		$layout_array['post_html'] = $chat_layout_post_entry;
		
		if (isset($chat_layout_user_info_entry))
			$layout_array['user_info_entry'] = $chat_layout_user_info_entry;
		else
			$layout_array['user_info_entry'] = "<li>!!INFO_HEAD!!: !!INFO!!</li>";
				
		if (isset($chat_layout_notify_user))
			$layout_array['notify_user'] = $chat_layout_notify_user;
				
		if (isset($chat_layout_channel_tab))
			$layout_array['channel_tab'] = $chat_layout_channel_tab;
		else
			$layout_array['channel_tab'] = "<li id='!!ID!!'><a href='javascript:void(0);' onclick='!!CHANNEL_CHANGE_FUNCTION!!'>!!CHANNEL!!</a></li>";
				
		$layout_array['notify_html'] = $chat_layout_notify_entry;
		$layout_array['javascript_functions'] = $chat_layout_functions;
		
		$this->arr = $layout_array;
	}
}
?> 
