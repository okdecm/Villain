<?php

	namespace Villain\HTTP;

	class Request
	{
		public $Method;
		public $Path;
		public $Query;

		public function __construct(string $method = null, string $path = null, $query = null)
		{
			if($method == null)
			{
				$method = $_SERVER["REQUEST_METHOD"];
			}

			if($path == null)
			{
				$path = $_SERVER["REQUEST_URI"];
			}

			if(!is_string($query) && !is_array($query))
			{
				$query = $_SERVER["QUERY_STRING"];
			}

			if(is_string($query))
			{
				parse_str($query, $query);
			}

			$this->Method = $method;
			$this->Path = $path;
			$this->Query = $query;
		}
	}

?>