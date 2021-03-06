<?php

class SiPacCommand_help extends SiPacCommand
{
	public $usage = "/help [<command>]";
	public $description = "Returns all commands available, or the syntax and description of a given command.";

	public function check_permission()
	{
		return true;
	}
	public function execute()
	{
	
		$command_files = $this->get_default_commands();
		$command_files = array_merge($command_files, $this->get_custom_commands());
		
		foreach ($command_files as $class_name => $file)
		{
			include_once($file);
			if (class_exists($class_name) AND empty($this->parameters) OR class_exists($class_name) AND str_replace("SiPacCommand_", "", $class_name)  == $this->parameters)
			{
				$check_command = new $class_name;
				$check_command->set_variables($this->chat, false, false);
				if ($check_command->check_permission() === true)
				{
					if (!empty($command_syntax))
						$command_syntax = $command_syntax."<br>";
					else
						$command_syntax = "";
			
					$command_syntax = $command_syntax.htmlentities($check_command->usage);
						
					if (!empty($this->parameters) AND isset($check_command->description))
						$command_syntax = $command_syntax."<br><i>".htmlentities($check_command->description)."</i>";
				}
			}
		}

		if (empty($command_syntax))
			$command_syntax = "<||command-not-found-text|".htmlentities($this->parameters)."||>";
		else if (empty($this->parameters))
			$command_syntax = "<||command-list-head||><br>".$command_syntax;
		else
			$command_syntax = "<||command-syntax-head||><br>".$command_syntax;
	
		return array("info_type"=>"info", "info_text"=>$command_syntax, "info_nohide"=>true);
	}
	private function get_custom_commands()
	{
		$command_files = array();
		foreach ($this->chat->settings->get('custom_commands') as $command)
		{
			$class_name = "SiPacCommand_".$command;
			$path = dirname(__FILE__)."/../../../conf/command/".$class_name.".php";
			if (file_exists($path))
				$command_files[$class_name] = $path;
		}
		return $command_files;
	}
	private function get_default_commands()
	{
		$command_files = array();
		
		$command_list = $this->chat->command->command_list;
		foreach ($command_list as $class_name)
		{
			$path = dirname(__FILE__)."/".$class_name.".php";
			$command_files[$class_name] = $path;
		}
		
		return $command_files;
	}
}

?>