<?php
namespace b2;

abstract class Quote
{
	public function value($any)
	{
		if (is_array($any)) {
			throw new Exception('Value cannot be an array');
		} elseif ($any instanceof Literal) {
			return $any->toString($this);
		} else {
			return $this->constant($any);
		}
	}

	public function values(array $list)
	{
		return implode(', ', array_map([$this, 'value'], $list));
	}

	public function identifier($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'identifier'], $any));
		} elseif ($any instanceof Literal) {
			return $any->toString($this);
		} else {
			return implode('.', array_map([$this, 'entity'], explode('.', $any)));
		}
	}

	public function entity($name)
	{
		return "`$name`";
	}

	abstract public function constantString($string);

	public function constant($any)
	{
		if (is_null($any)) {
			return $this->constantNull();
		} elseif (is_bool($any)) {
			return $this->constantBool($any);
		} else {
			return $this->constantString((string)$any);
		}
	}

	/**
	 * Create instance for specified connection.
	 * @param \mysqli $dbConnection
	 * @return \b2\Quote
	 * @throws Exception
	 */
	static public function createFromMysqli(\mysqli $dbConnection)
	{
		return new quote\Mysqli($dbConnection);
	}

	public function constantNull()
	{
		return 'NULL';
	}

	public function constantBool($bool)
	{
		return $bool ? '1' : '0';
	}
}
