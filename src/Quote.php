<?php
namespace b2;

abstract class Quote
{
	public function value($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'value'], $any));
		} elseif ($any instanceof Literal) {
			return $any->toString($this);
		} else {
			return $this->constant($any);
		}
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

	abstract public function constant($any);

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
}
