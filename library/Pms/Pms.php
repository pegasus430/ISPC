<?php

	class Pms {

		public static function __autoload($className)
		{
			if(file_exists(realpath(APPLICATION_PATH . "/../library/" . str_replace("_", "/", $className) . ".php")))
			{
				// require_once realpath(APPLICATION_PATH."/../library/".str_replace("_","/",$className.".php"));
			}
			elseif(file_exists(realpath(APPLICATION_PATH . "/../" . str_replace("_", "/", $className) . ".php")))
			{
				require_once realpath(APPLICATION_PATH . "/../" . str_replace("_", "/", $className . ".php"));
			}
		}
	}

?>