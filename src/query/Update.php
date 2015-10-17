<?php
namespace d2\query;

use d2\literal\Where;
use d2\Exception;

class Update extends \d2\Query
{
	use \d2\ability\HasWhere;
	use \d2\ability\HasOrder;
	use \d2\ability\HasLimit;

	/**
	 *
	 * @var d2\literal[]
	 */
	private $sets = [];

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(\d2\Quote $quote)
	{
		if (!$this->sets)
			throw new Exception('Empty set');

		$sql = 'UPDATE ' . $this->table->toString($quote);

		$sets = [];
		foreach ($this->sets as $expression) {
			$sets[] = $expression->toString($quote);
		}

		$sql .= ' SET ' . implode(', ', $sets);

		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

	public function set($fieldNameOrPrepared, $valueOrBinds = null/* ... */) {
		$expressions = \d2\ability\WhereUpdateCommon::extractExpressionsFromArgs(func_get_args());

		$this->sets = array_merge($this->sets, $expressions);
	}
}
