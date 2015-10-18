<?php
namespace b2\literal;

use b2\Exception;

class PlainSql extends \b2\Literal
{
	static $placeholderRegex = '/\?|:([a-z0-9_]+)/';
	private $sql;
	private $binds = [];

	public function __construct($sql, array $binds = [])
	{
		if (!is_string($sql))
			throw new Exception('SQL must be a string');

		foreach ($binds as $b) {
			if (!($b instanceof \b2\Literal))
				throw new Exception('Bind must be a Literal');
		}

		$this->sql = $sql;
		$this->binds = $binds;
	}

	public function toString(\b2\Quote $quote)
	{
		$n = 0;

		$usedKeys = [];

		$plain = preg_replace_callback(
			self::$placeholderRegex, function($w) use(&$n, &$usedKeys, $quote) {
			$key = null;

			if ($w[0] === '?') {
				$key = $n;
				$n++;
			} else {
				$key = ':' . $w[1];
			}

			if (!array_key_exists($key, $this->binds)) {
				throw new Exception("Bind key $key was not found");
			}

			$usedKeys[$key] = $key;

			return $this->binds[$key]->toString($quote);
		}, $this->sql
		);

		$unusedKeys = array_diff(array_keys($this->binds), $usedKeys);

		if ($unusedKeys) {
			throw new Exception('Too many binds: ' . implode(', ', $unusedKeys));
		}

		return $plain;
	}

	static public function hasPlaceholders($sql)
	{
		return !!preg_match(self::$placeholderRegex, $sql);
	}

}
