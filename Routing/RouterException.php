<?php

	namespace Villain\Routing;

	class RouterException extends \Exception
	{
		public $Method = null;
		public $Path = null;
		public $Route = null;

		const UNKNOWN_ROUTE = 0;

		public function __construct($type, ... $args)
		{
			switch($type)
			{
				case $this::UNKNOWN_ROUTE:
					foreach($args as $argInx => $arg)
					{
						switch($argInx)
						{
							case 0:
								$this->Method = $arg;
							break;

							case 1:
								$this->Path = $arg;
							break;
						}
					}

					parent::__construct("Unknown route supplied");
				break;
			}
		}
	}

?>