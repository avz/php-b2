<?php
namespace b2\query;

class Delete extends \b2\Query
{
	use \b2\ability\HasWhere;
	use \b2\ability\HasOrder;
	use \b2\ability\HasLimit;

	public function __construct($table = null)
	{
		parent::__construct($table);

		$this->where = new \b2\literal\Where;
	}

	public function toString(\b2\Quote $quote)
	{
		$sql = 'DELETE FROM ' . $this->needTable()->toString($quote);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

}
