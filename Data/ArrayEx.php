<?php

	namespace Villain\Data;

	class ArrayEx
	{
		public static function Merge($defaults, $array, $removeUnknownKeys = false)
		{
			if($removeUnknownKeys)
			{
				$array = array_intersect_key($array, $defaults);
			}

			return array_merge(
				$defaults,
				$array
			);
		}
	}

?>