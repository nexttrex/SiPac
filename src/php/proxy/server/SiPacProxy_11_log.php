<?php

class SiPacProxy_log extends SiPacProxy
{
	public function execute()
	{
		$type = $this->post['type'];
		$chat_user = $this->post['user'];
		$message = $this->post['message'];
		$channel = $this->chat->channel->get_name($this->post['channel']);
		
		$log_date     = date("Y-m-d H:i:s", $this->post['time']);
		$log_time     = date("H:i:s", $this->post['time']);
		$log_year   = date("Y", $this->post['time']);
		$log_filename = date("m", $this->post['time']);
		
  
		$log_folder = "../../log/";
		
		if (substr(decoct(fileperms($log_folder)), -3) == 777)
		{
			$log_folder = $log_folder . $this->chat->decode_id() . "/";
				
			if (is_dir($log_folder) == false)
				mkdir($log_folder, 0777);
				
				
			$log_folder = $log_folder . $log_year;
				
			if (is_dir($log_folder) == false OR is_writable($log_folder))
			{
				if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
					$ip = $_SERVER['REMOTE_ADDR'];
				else
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				
				if ($type == 0)
					$type_name = "<||log-message||>"; //Message
				else if ($type == 1)
					$type_name = "<||log-info||>"; //Info
				else
					$type_name = "?";
				
				if (is_dir($log_folder) == false)
					mkdir($log_folder, 0777);
				
				$chat_log_file = fopen($log_folder . '/' . $log_filename . '.log', "a+");
				
				$chat_log      = "\n[$log_date] [$channel] $chat_user: ". html_entity_decode($message) . "		| $ip";
				
				fwrite($chat_log_file, $this->chat->language->translate($chat_log, $this->chat->settings->get('log_language')));
				fclose($chat_log_file);
				}
				else
					$this->chat->debug->add( "Wrong Permissions in Folder \"$log_folder\". Please change it to 777!", 1);
			}
			
		return $this->post;
	}
}
?>