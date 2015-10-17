<?php
namespace d2;

abstract class Query extends Literal
{
	protected $table;

	public function __construct($table)
	{
		if (is_string($table)) {
			$this->table = new \d2\literal\Identifier($table);
		} elseif ($table instanceof \d2\Literal) {
			$this->table = $table;
		} else {
			throw new Exception('Only strings and Literals allowed in table name');
		}
	}
}
