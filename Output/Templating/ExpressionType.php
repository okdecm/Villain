<?php

	namespace Villain\Output\Templating;

	final class ExpressionType
	{
		const ET_UNKNOWN = 0;
		const ET_FOR_EACH = 1;
		const ET_END_FOR_EACH = 2;
		const ET_IF = 3;
		const ET_ELSE_IF = 4;
		const ET_ELSE = 5;
		const ET_END_IF = 6;
	}

?>