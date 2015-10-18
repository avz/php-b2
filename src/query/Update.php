<?php
namespace b2\query;

use b2\literal\Where;
use b2\Exception;

class Update extends \b2\Query
{
	use \b2\ability\HasWhere;
	use \b2\ability\HasOrder;
	use \b2\ability\HasLimit;

	/**
	 *
	 * @var b2\literal[]
	 */
	private $sets = [];

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(\b2\Quote $quote)
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
		$expressions = \b2\ability\WhereUpdateCommon::extractExpressionsFromArgs(func_get_args());

		$this->sets = array_merge($this->sets, $expressions);

		return $this;
	}
}
