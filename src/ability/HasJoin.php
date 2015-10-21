<?php
namespace b2\ability;

use b2\Literal;
use b2\literal\Identifier;
use b2\literal\Where;
use b2\Exception;

class JoinInfo {
	/**
	 *
	 * @var Literal
	 */
	public $table;

	/**
	 *
	 * @var string
	 */
	public $type;

	/**
	 *
	 * @var Literal
	 */
	public $condition;

	public function __construct($type, Literal $table, Literal $condition)
	{
		$this->table = $table;
		$this->type = $type;
		$this->condition = $condition;
	}
}

trait HasJoin {
	/**
	 *
	 * @var JoinInfo[]
	 */
	private $joins = [];

	public function innerJoin($table, $condition, $binds = null) {
		$t = 'INNER';

		if(func_num_args() > 2)
			return $this->join($t, $table, $condition, $binds);
		else
			return $this->join($t, $table, $condition);
	}

	public function leftJoin($table, $condition, $binds = null) {
		$t = 'LEFT';

		if(func_num_args() > 2)
			return $this->join($t, $table, $condition, $binds);
		else
			return $this->join($t, $table, $condition);
	}

	private function join($type, $table, $condition) {
		$tableExpression = null;

		if ($table instanceof Literal)
			$tableExpression = $table;
		elseif (is_string($table))
			$tableExpression = new Identifier($table);
		else
			throw new Exception('Table name or Literal expected');

		$condArgs = array_slice(func_get_args(), 2);
		$joinCondition = WhereUpdateCommon::extractExpressions($condArgs);

		$where = new Where;
		foreach ($joinCondition as $jc) {
			$where->addAnd($jc);
		}

		$info = new JoinInfo($type, $tableExpression, $where->getExpression());

		$this->joins[] = $info;

		return $this;
	}

	private function joinsToString(\b2\Quote $quote) {
		$list = [];

		foreach ($this->joins as $joinInfo) {
			$list[] =
				$joinInfo->type . ' JOIN ' . $joinInfo->table->toString($quote)
				. ' ON ' . $joinInfo->condition->toString($quote)
			;
		}

		return implode(' ', $list);
	}

	protected function joinsIsEmpty() {
		return !$this->joins;
	}

	protected function joinsConcatSql(\b2\Quote $quote, $sql) {
		if ($this->joinsIsEmpty())
			return $sql;

		return $sql . ' ' . $this->joinsToString($quote);
	}
}
