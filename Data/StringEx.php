<?php

	namespace Villain\Data;

	class StringEx
	{
		public static function CanCastToString($value)
		{
			// Taken from: https://stackoverflow.com/a/5496674
			if(!is_array($value) && (!is_object($value) && settype($value, "string") !== false) || (is_object($value) && method_exists($value, "__toString")))
			{
				return true;
			}

			return false;
		}
	}

?>