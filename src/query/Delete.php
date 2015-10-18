<?php
namespace b2\query;

class Delete extends \b2\Query
{
	use \b2\ability\HasWhere;
	use \b2\ability\HasOrder;
	use \b2\ability\HasLimit;

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new \b2\literal\Where;
	}

	public function toString(\b2\Quote $quote)
	{
		$sql = 'DELETE FROM ' . $this->table->toString($quote);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

}
