<?php

	namespace Villain\HTTP;

	class Response
	{
		public static function SetStatusCode($statusCode)
		{
			http_response_code($statusCode);
		}
	}

?>