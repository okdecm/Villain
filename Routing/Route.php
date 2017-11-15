<?php

	namespace Villain\Routing;

	class Route
	{
		private $_methods;
		private $_pattern;
		private $_callback;

		private $_patternRegex;

		private $_callbackParsers = array();

		public function __construct($methods, $pattern, $callback)
		{
			if(!is_array($methods))
			{
				$methods = array($methods);
			}

			$this->_methods = $methods;
			$this->_pattern = $pattern;
			$this->_callback = $callback;

			$this->_patternRegex = "/^" . preg_replace("/\\\{[^\}]+\\\}/", '([^\/]+)', preg_quote($this->TrimPath($this->_pattern), "/")) . "$/";
		}

		public function Execute($path)
		{
			$args = array();

			preg_match($this->_patternRegex, $this->TrimPath($path), $args);

			array_shift($args);

			call_user_func_array($this->_callback, $args);
		}

		public function Matches($method, $path)
		{
			if(!in_array($method, $this->_methods) && !in_array("any", $this->_methods))
			{
				return false;
			}

			return preg_match($this->_patternRegex, $this->TrimPath($path));
		}

		public function TrimPath($path)
		{
			if($path[(strlen($path) - 1)] == '/')
			{
				$path = substr($path, 0, (strlen($path) - 1));
			}

			return $path;
		}
	}

?>