<?php

	namespace Villain;

	spl_autoload_register(
		function($class)
		{
			$class = explode("\\", $class);

			if($class[0] == "Villain")
			{
				array_shift($class);

				require dirname(__FILE__) . DIRECTORY_SEPARATOR . implode("\\", $class) . ".php";
			}
		}
	);

?>