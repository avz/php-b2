<?php
namespace d2\tests\mock;

class Quote extends \d2\Quote
{
	public function value($any) {
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'value'], $any));
		} elseif ($any instanceof Literal) {
			return $any->toString($this);
		} elseif (is_null($any)) {
			return 'NULL';
		} elseif (is_bool($any)) {
			return (string)(int)$any;
		} else {
			return "'" . addslashes($any) . "'";
		}
	}

	public function identifier($any) {
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'identifier'], $any));
		} else {
			return '`' . str_replace('.', '`.`', (string)$any) . '`';
		}
	}
}
