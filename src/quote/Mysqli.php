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

	public function constantString($string)
	{
		return "'" . $this->connection->escape_string($string) . "'";
	}
}
