<?php
namespace b2\tests\mock;

class Quote extends \b2\Quote
{
	public function constant($any) {
		if (is_null($any)) {
			return 'NULL';
		} elseif (is_bool($any)) {
			return (string)(int)$any;
		} else {
			return "'" . addslashes($any) . "'";
		}
	}
}
