<?php
namespace b2\literal;

use b2\Exception;

class PlainSql extends \b2\Literal
{
	static $placeholderRegex = '/\?{1,2}|((::?)[a-z0-9_]+)/';
	private $sql;

	/**
	 * @var \b2\Literal[]
	 */
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
			self::$placeholderRegex,
			function($w) use(&$n, &$usedKeys, $quote) {
				$key = null;
				$mustBeList = false;

				if ($w[0] === '?') {
					$key = $n;
					$n++;
				} else if ($w[0] === '??') {
					$key = $n;
					$n++;
					$mustBeList = true;
				} else if ($w[2] === ':') {
					$key = $w[1];
				} else if ($w[2] === '::') {
					$key = $w[1];
					$mustBeList = true;
				} else {
					throw new Exception('Impossible case');
				}

				if (!array_key_exists($key, $this->binds)) {
					throw new Exception("Bind key $key was not found");
				}

				$usedKeys[$key] = $key;

				$value = $this->binds[$key];

				if ($mustBeList) {
					if (!($value instanceof AnyList)) {
						throw new Exception('AnyList expected, but ' . get_class($value) . ' found');
					}
				} else {
					if ($value instanceof AnyList) {
						throw new Exception('Literal expected, but AnyList found');
					}
				}

				return $value->toString($quote);
			},
			$this->sql
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
