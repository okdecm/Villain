<?php

	namespace Villain\Routing;

	class Router
	{
		private static $_routes = array();

		public static function Route(string $method, string $path)
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

		public static function Delete(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("delete", $pattern, $callback, $isRegex);
		}

		public static function Get(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("get", $pattern, $callback, $isRegex);
		}

		public static function Patch(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("patch", $pattern, $callback, $isRegex);
		}

		public static function Post(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("post", $pattern, $callback, $isRegex);
		}

		public static function Put(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("put", $pattern, $callback, $isRegex);
		}

		public static function Any(string $pattern, callable $callback, bool $isRegex = false)
		{
			self::Add("any", $pattern, $callback, $isRegex);
		}

		public static function Add($methods, string $pattern, callable $callback, bool $isRegex = false)
		{
			$route = new Route($methods, $pattern, $callback, $isRegex);
			
			self::AddRoute($route);

			return $route;
		}

		public static function AddRoute(Route $route)
		{
			array_push(self::$_routes, $route);
		}
	}

?>