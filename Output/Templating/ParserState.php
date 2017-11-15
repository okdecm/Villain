<?php

	namespace Villain\Output\Templating;

	final class ParserState
	{
		const NORMAL = 0;
		const END_OF_BRANCH = 1;
		const PREPEND_TOKEN = 1 << 2;
	}

?>