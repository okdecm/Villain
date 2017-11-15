<?php

	namespace Villain\Routing;

	class Router
	{
		private static $_routes = array();

		public static function Route($method, $path)
		{
			$routes = array_filter(
				self::$_routes,
				function($route) use ($method, $path)
				{
					return $route->Matches($method, $path);
				}
			);

			if(!empty($routes))
			{
				current($routes)->Execute($path);
			}
			else
			{
				throw new RouterException(RouterException::UNKNOWN_ROUTE, $method, $path);
			}
		}

		public static function Delete($pattern, $callback, $isRegex = false)
		{
			self::Add("delete", $pattern, $callback, $isRegex);
		}

		public static function Get($pattern, $callback, $isRegex = false)
		{
			self::Add("get", $pattern, $callback, $isRegex);
		}

		public static function Patch($pattern, $callback, $isRegex = false)
		{
			self::Add("patch", $pattern, $callback, $isRegex);
		}

		public static function Post($pattern, $callback, $isRegex = false)
		{
			self::Add("post", $pattern, $callback, $isRegex);
		}

		public static function Put($pattern, $callback, $isRegex = false)
		{
			self::Add("put", $pattern, $callback, $isRegex);
		}

		public static function Any($pattern, $callback, $isRegex = false)
		{
			self::Add("any", $pattern, $callback, $isRegex);
		}

		public static function Add($methods, $pattern, $callback, $isRegex = false)
		{
			$callback = self::ParseCallback($callback);

			if($callback == null)
			{
				throw new Exception("Failed to parse callback");
			}

			$route = new Route($methods, $pattern, $callback, $isRegex);
			
			self::AddRoute($route);

			return $route;
		}

		public static function AddRoute($route)
		{
			array_push(self::$_routes, $route);
		}

		public static function ParseCallback($callback)
		{
			if(is_callable($callback))
			{
				return $callback;
			}
			else if(is_array($callback) && count($callback) == 2)
			{
				return $callback;
			}

			return null;
		}
	}

?>