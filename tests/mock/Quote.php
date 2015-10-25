<?php
namespace b2\tests\mock;

class Quote extends \b2\Quote
{
	public function constantString($any) {
		return "'" . addslashes($any) . "'";
	}
}
