<?php

	namespace Villain\HTTP;

	class Response
	{
		public static function SetStatusCode(int $statusCode)
		{
			http_response_code($statusCode);
		}
	}

?>