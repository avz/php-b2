<?php
namespace b2;

abstract class Query extends Literal
{
	protected $table = null;

	public function __construct($table = null)
	{
		if ($table)
			$this->table($table);
	}

	public function table($table)
	{
		if (is_string($table)) {
			$this->table = new \b2\literal\Identifier($table);
		} elseif ($table instanceof \b2\Literal) {
			$this->table = $table;
		} else {
			throw new Exception('Only strings and Literals allowed in table name');
		}

		return $this;
	}

	protected function needTable()
	{
		if ($this->table === null)
			throw new Exception("Table is not specified");

		return $this->table;
	}
}
