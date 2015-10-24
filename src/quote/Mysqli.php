<?php
namespace b2\quote;

class Mysqli extends \b2\Quote
{
	/**
	 *
	 * @var \mysqli
	 */
	private $connection;

	public function __construct(\mysqli $connection)
	{
		$this->connection = $connection;
	}

	public function constant($any)
	{
		if (is_null($any)) {
			return 'NULL';
		} elseif (is_bool($any)) {
			return (string)(int)$any;
		} else {
			return "'" . $this->connection->escape_string($any) . "'";
		}
	}
}
