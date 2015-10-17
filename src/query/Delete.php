<?php
namespace d2\query;

class Delete extends \d2\Query
{
	use \d2\ability\HasWhere;
	use \d2\ability\HasOrder;
	use \d2\ability\HasLimit;

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new \d2\literal\Where;
	}

	public function toString(\d2\Quote $quote)
	{
		$sql = 'DELETE FROM ' . $this->table->toString($quote);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

}
