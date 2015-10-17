<?php

namespace common\d2;

class Exception extends \Exception
{

}

class Quote
{

	public function value($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'value'], $any));
		} elseif ($any instanceof Literal) {
			return $any->toString($this);
		} elseif (is_null($any)) {
			return 'NULL';
		} else {
			return "'" . addslashes($any) . "'";
		}
	}

	public function identifier($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'identifier'], $any));
		} else {
			return '`' . str_replace('.', '`.`', (string)$any) . '`';
		}
	}

}

abstract class Literal
{

	abstract public function toString(Quote $quote);

	protected function quote($any)
	{

	}

}

class PlainSql extends Literal
{
	static $placeholderRegex = '/\?|:([a-z0-9_]+)/';
	private $sql;
	private $binds = [];

	public function __construct($sql, array $binds = [])
	{
		if (!is_string($sql))
			throw new Exception('SQL must be a string');

		foreach ($binds as $b) {
			if (!($b instanceof Literal))
				throw new Exception('Bind must be a Literal');
		}

		$this->sql = $sql;
		$this->binds = $binds;
	}

	public function toString(Quote $quote)
	{
		$n = 0;

		$usedKeys = [];

		$plain = preg_replace_callback(
			self::$placeholderRegex, function($w) use(&$n, &$usedKeys, $quote) {
			$key = null;

			if ($w[0] === '?') {
				$key = $n;
				$n++;
			} else {
				$key = ':' . $w[1];
			}

			if (!array_key_exists($key, $this->binds)) {
				throw new Exception("Bind key $key was not found");
			}

			$usedKeys[$key] = $key;

			return $this->binds[$key]->toString($quote);
		}, $this->sql
		);

		$unusedKeys = array_diff(array_keys($this->binds), $usedKeys);

		if ($unusedKeys) {
			throw new Exception('Too many binds: ' . implode(', ', $unusedKeys));
		}

		return $plain;
	}

	static public function hasPlaceholders($sql)
	{
		return !!preg_match(self::$placeholderRegex, $sql);
	}

}

class Identifier extends Literal
{
	public $identifier;

	public function __construct($identifier)
	{
		if (!is_string($identifier))
			throw new Exception('Identifier must be a string');

		$this->identifier = $identifier;
	}

	public function toString(Quote $quote)
	{
		return $quote->identifier($this->identifier);
	}

}

class Constant extends Literal
{
	public $constant;

	public function __construct($constant)
	{
		if (is_object($constant))
			throw new Exception('Object is not allowed');

		$this->constant = $constant;
	}

	public function toString(Quote $quote)
	{
		return $quote->value($this->constant);
	}

}

class BiOperation extends Literal
{
	public $nodes = [];
	public $left;
	public $right;
	public $operator;

	public function __construct(Literal $left, $operator, Literal $right)
	{
		$this->left = $left;
		$this->right = $right;
		$this->operator = $operator;
	}

	public function toString(Quote $quote)
	{
		return '(' . $this->left->toString($quote)
			. ' ' . $this->operator . ' '
			. $this->right->toString($quote) . ')'
		;
	}

}

class Call extends Literal
{
	public $functionName;
	public $args = [];

	public function __construct($functionName, array $args = [])
	{
		$this->functionName = $functionName;
		$this->args = $args;
	}

	public function toString(Quote $quote)
	{
		$args = [];
		foreach ($this->args as $arg) {
			if ($arg instanceof Literal)
				$args[] = $arg->toString($quote);
			else
				$args[] = $quote->value($arg);
		}

		return $this->functionName . '(' . implode(', ', $args) . ')';
	}

}

class Where extends Literal
{
	private $expression = null;

	public function __construct(Literal $expression = null)
	{
		$this->expression = $expression;
	}

	public function addAnd(Literal $expression)
	{
		return $this->addToEnd('AND', $expression);
	}

	public function addOr(Literal $expression)
	{
		return $this->addToEnd('OR', $expression);
	}

	private function addToEnd($operator, Literal $expression)
	{
		if ($this->expression) {
			$this->expression = new BiOperation($this->expression, $operator, $expression);
		} else {
			$this->expression = $expression;
		}
	}

	public function isEmpty()
	{
		return !$this->expression;
	}

	public function toString(Quote $quote)
	{
		if (!$this->expression)
			throw new Exception('Empty WHERE');

		return $this->expression->toString($quote);
	}

	public function getExpression() {
		return $this->expression;
	}
}

class In extends Literal
{
	private $expression;
	private $cases = [];

	public function __construct(Literal $expression, array $cases = [])
	{
		$this->expression = $expression;
		$this->cases = $cases;
	}

	public function addCase(Literal $case)
	{
		$this->cases[] = $case;
	}

	public function toString(Quote $quote)
	{
		if (!$this->cases)
			throw new Exception('IN is empty');

		$call = new Call('IN', $this->cases);

		return $this->expression->toString($quote) . ' ' . $call->toString($quote);
	}

}

abstract class Query extends Literal
{
	protected $table;

	public function __construct($table)
	{
		if (is_string($table)) {
			$this->table = new Identifier($table);
		} elseif ($table instanceof Literal) {
			$this->table = $table;
		} else {
			throw new Exception('Only strings and Literals allowed in table name');
		}
	}

	protected function quote($any)
	{
		if (is_array($var)) {

		} else {

		}
	}

}

class Insert extends Query
{
	private $keys = [];
	private $rows = [];
	private $ignore = false;
	private $replace = false;
	private $onDuplicateKeyUpdates = [];
	private $onDuplicateKeyUpdateAll = false;

	public function ignore()
	{
		$this->ignore = true;

		return $this;
	}

	public function replace()
	{
		$this->replace = true;

		return $this;
	}

	public function row(array $row)
	{
		if (!$row)
			throw new Exception('Empty row');

		ksort($row);

		$keys = array_keys($row);

		if (!$this->keys) {
			$this->keys = $keys;
		} else {
			if ($keys !== $this->keys)
				throw new Exception("All rows in single query must have identical fields");
		}

		$this->rows[] = array_values($row);

		return $this;
	}

	public function values(array $rows)
	{
		foreach ($rows as $row)
			$this->row($row);

		return $this;
	}

	public function onDuplicateKeyUpdate($any = null)
	{
		if (is_null($any)) {
			// update all of columns with `column` = VALUES(`column`)
			$this->onDuplicateKeyUpdateAll = true;
		} else {
			if (!is_array($any))
				$any = [$any];

			// list of columns to `column` = VALUES(`column`)
			foreach ($any as $k => $v) {
				if (is_numeric($k)) {
					$this->onDuplicateKeyUpdates[$v] = new Call('VALUES', [$v instanceof Literal ? $v : new Identifier($v)]);
				} else {
					$this->onDuplicateKeyUpdates[$k] = $v instanceof Literal ? $v : new Constant($v);
				}
			}
		}

		return $this;
	}

	public function toString(Quote $quote)
	{
		if (!$this->rows) {
			throw new Exception('Empty INSERT');
		}

		$sql = '';
		if ($this->replace)
			$sql = 'REPLACE';
		else
			$sql = 'INSERT';

		if ($this->ignore)
			$sql .= ' IGNORE';

		$sql .= ' INTO ' . $this->table->toString($quote);

		$sql .= '(' . $quote->identifier($this->keys) . ')';

		$sql .= ' VALUES ';

		$strings = [];
		foreach ($this->rows as $row) {
			/*
			 * Значения в строках уже отсортированы по названием филдов,
			 * так что тут больше ничего делать не надо
			 */
			$vals = [];
			foreach ($row as $f) {
				if ($f instanceof Literal)
					$vals[] = $f->toString($quote);
				else
					$vals[] = $quote->value($f);
			}

			$strings[] = '(' . implode(', ', $vals) . ')';
		}

		$sql .= implode(', ', $strings);

		if ($this->onDuplicateKeyUpdateAll || $this->onDuplicateKeyUpdates) {
			$sql .= ' ON DUPLICATE KEY UPDATE';

			$onDup = $this->onDuplicateKeyUpdates;

			if ($this->onDuplicateKeyUpdateAll) {
				foreach ($this->keys as $column) {
					if (isset($onDup[$column]))
						continue; // override

					$onDup[$column] = new Call('VALUES', [new Identifier($column)]);
				}
			}

			ksort($onDup);

			$strings = [];

			foreach ($onDup as $field => $value) {
				$strings[] = $quote->identifier($field) . ' = ' . $value->toString($quote);
			}

			$sql .= ' ' . implode(', ', $strings);
		}

		return $sql;
	}

}

abstract class WhereUpdateCommon {
	static private function prepared($prepared, array $binds = [])
	{
		$binds = array_map(
			function ($value) {
			return new Constant($value);
		}, $binds
		);

		return new PlainSql($prepared, $binds);
	}

	static private function fieldEqual($columnName, $columnValue)
	{
		$e = null;

		if (is_array($columnValue)) {
			$cases = array_map(
				function ($value) {
				if ($value instanceof Literal)
					return $value;
				else
					return new Constant($value);
			}, $columnValue
			);

			$e = new In(new Identifier($columnName), $cases);
		} else {
			$v = null;
			if ($columnValue instanceof Literal)
				$v = $columnValue;
			else
				$v = new Constant($columnValue);

			$e = new BiOperation(new Identifier($columnName), '=', $v);
		}

		return $e;
	}

	static public function extractExpressionsFromArgs(array $args)
	{
		$numArgs = sizeof($args);

		if (!$numArgs)
			throw new Exception('Not enough arguments');

		$fieldNameOrPrepared = $args[0];
		$valueOrBinds = null;

		if ($numArgs > 1)
			$valueOrBinds = $args[1];

		$expressions = [];

		if (is_string($fieldNameOrPrepared)) {
			if (PlainSql::hasPlaceholders($fieldNameOrPrepared) || $numArgs === 1) {
				if ($numArgs == 1) {
					$valueOrBinds = [];
				} else {
					if (!is_array($valueOrBinds))
						throw new Exception('Binds must be an array');
				}

				$expressions[] = self::prepared('(' . $fieldNameOrPrepared . ')', $valueOrBinds);
			} else {
				if ($numArgs !== 2)
					throw new Exception('Exactly 2 arguments expected');

				$expressions[] = self::fieldEqual($fieldNameOrPrepared, $valueOrBinds);
			}
		} else if ($fieldNameOrPrepared instanceof Literal) {

			$expressions[] = $fieldNameOrPrepared;

		} else if (is_array($fieldNameOrPrepared)) {

			foreach ($fieldNameOrPrepared as $column => $value) {
				$expressions[] = self::fieldEqual($column, $value);
			}
		} else {
			throw new Exception('Incorrect expression definition');
		}

		return $expressions;
	}
}

trait Whereable
{
	/**
	 *
	 * @var Where
	 */
	private $where;

	public function where($fieldNameOrPrepared, $valueOrBinds = null/* ... */)
	{
		$where = clone $this->where;

		$expressions = WhereUpdateCommon::extractExpressionsFromArgs(func_get_args());

		foreach ($expressions as $expression) {
			$where->addAnd($expression);
		}

		$this->where = $where;

		return $this;
	}

	protected function whereIsEmpty()
	{
		return $this->where->isEmpty();
	}

	private function whereToString(Quote $quote)
	{
		return $this->where->toString($quote);
	}

	protected function whereConcatSql(Quote $quote, $sql) {
		if (!$this->whereIsEmpty())
			$sql .= ' WHERE ' . $this->whereToString($quote);

		return $sql;
	}
}

trait Limitable
{
	private $limit = null;
	private $offset = null;

	public function limit($limit)
	{
		if (!is_int($limit) || $limit <= 0)
			throw new Exception('LIMIT must be positive int');

		$this->limit = $limit;
	}

	public function offset($offset)
	{
		if (!is_int($offset) || $offset <= 0)
			throw new Exception('OFFSET must be positive int');

		$this->offset = $offset;
	}

	protected function limitIsEmpty()
	{
		return $this->limit === null && $this->offset === null;
	}

	private function limitToString(Quote $quote)
	{
		if ($this->offset !== null && $this->limit === null)
			throw new Exception('OFFSET without LIMIT');

		$sql = null;

		if ($this->limit === null)
			return $sql;

		if ($this->offset !== null) {
			$sql = sprintf("LIMIT %u OFFSET %u", $this->limit, $this->offset);
		} else {
			$sql = sprintf("LIMIT %u", $this->limit);
		}

		return $sql;
	}

	protected function limitConcatSql(Quote $quote, $sql) {
		if (!$this->limitIsEmpty())
			$sql .= ' ' . $this->limitToString($quote);

		return $sql;
	}
}

trait Orderable
{
	private $orders = [];

	public function orderBy($column, $direction = 'ASC')
	{
		$e = null;

		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new Exception('Direction must be ASC or DESC');
		}

		if (is_string($column)) {
			$e = new Identifier($column);
		} elseif ($column instanceof Literal) {
			$e = $column;
		} elseif ($column === null) {
			$e = new Constant(null);

			if ($direction !== 'ASC')
				throw new Exception('ORDER BY NULL DESC is not allowed');

		} else {
			throw new Exception('Only string, null or Literal allowed');
		}

		$this->orders[] = [$e, $direction];
	}

	protected function orderIsEmpty()
	{
		return !$this->orders;
	}

	private function orderToString(Quote $quote)
	{
		$list = [];
		foreach ($this->orders as $o) {
			list($expression, $direction) = $o;

			$list[] = $expression->toString($quote) . ($direction !== 'ASC' ? ' ' . $direction : '');
		}

		if (!$list) {
			return null;
		}

		return 'ORDER BY ' . implode(', ', $list);
	}

	protected function orderConcatSql(Quote $quote, $sql) {
		if (!$this->orderIsEmpty())
			$sql .= ' ' . $this->orderToString($quote);

		return $sql;
	}
}

trait Groupable
{
	private $groups = [];

	public function groupBy($column, $direction = 'ASC')
	{
		$e = null;

		if ($direction !== 'ASC' && $direction !== 'DESC') {
			throw new Exception('Direction must be ASC or DESC');
		}

		if (is_string($column)) {
			$e = new Identifier($column);
		} elseif ($column instanceof Literal) {
			$e = $column;
		} else {
			throw new Exception('Only string  or Literal allowed');
		}

		$this->groups[] = [$e, $direction];
	}

	protected function groupIsEmpty()
	{
		return !$this->groups;
	}

	private function groupToString(Quote $quote)
	{
		$list = [];
		foreach ($this->groups as $o) {
			list($expression, $direction) = $o;

			$list[] = $expression->toString($quote) . ($direction !== 'ASC' ? ' ' . $direction : '');
		}

		if (!$list) {
			return null;
		}

		return 'GROUP BY ' . implode(', ', $list);
	}

	protected function groupConcatSql(Quote $quote, $sql) {
		if (!$this->groupIsEmpty())
			$sql .= ' ' . $this->groupToString($quote);

		return $sql;
	}
}

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

trait Joinable {
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
		$joinCondition = WhereUpdateCommon::extractExpressionsFromArgs($condArgs);

		$where = new Where;
		foreach ($joinCondition as $jc) {
			$where->addAnd($jc);
		}

		$info = new JoinInfo($type, $tableExpression, $where->getExpression());

		$this->joins[] = $info;
	}

	private function joinsToString(Quote $quote) {
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

	protected function joinsConcatSql(Quote $quote, $sql) {
		if ($this->joinsIsEmpty())
			return $sql;

		return $sql . ' ' . $this->joinsToString($quote);
	}
}

class Delete extends Query
{

	use Whereable;
	use Orderable;
	use Limitable;

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(Quote $quote)
	{
		$sql = 'DELETE FROM ' . $this->table->toString($quote);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

}

class Update extends Query
{
	use Whereable;
	use Orderable;
	use Limitable;

	/**
	 *
	 * @var Literal[]
	 */
	private $sets = [];

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(Quote $quote)
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
		$expressions = WhereUpdateCommon::extractExpressionsFromArgs(func_get_args());

		$this->sets = array_merge($this->sets, $expressions);
	}
}

class Select extends Query
{
	use Whereable;
	use Joinable;
	use Groupable;
	use Orderable;
	use Limitable;

	/**
	 *
	 * @var Literal
	 */
	private $columns = [];

	public function __construct($table)
	{
		parent::__construct($table);

		$this->where = new Where;
	}

	public function toString(Quote $quote)
	{
		if (!$this->columns) {
			throw new Exception('You must specify columns');
		}

		$sql = 'SELECT ';
		$sql .= $this->columnsToString($quote);
		$sql .= ' FROM ' . $this->table->toString($quote);

		$sql = $this->joinsConcatSql($quote, $sql);
		$sql = $this->whereConcatSql($quote, $sql);
		$sql = $this->groupConcatSql($quote, $sql);
		$sql = $this->orderConcatSql($quote, $sql);
		$sql = $this->limitConcatSql($quote, $sql);

		return $sql;
	}

	private function columnsToString(Quote $quote) {
		$list = [];

		foreach ($this->columns as $alias => $column) {
			if (ctype_digit((string)$alias) || $alias === '*') {
				$list[] = $column->toString($quote);
			} else {
				$list[] = $column->toString($quote) . ' AS ' . $quote->identifier($alias);
			}
		}

		return implode(', ', $list);
	}

	public function column($column, $alias = null) {
		$e = null;

		if ($alias !== null) {
			if (!is_string($alias)) {
				throw new Exception('Alias must be a string');
			}

			if ($alias === '*') {
				throw new Exception("Alias name '*' is not alowed");
			}

			if (ctype_digit((string)$alias)) {
				throw new Exception('Numerical aliases is not allowed');
			}

			if (isset($this->columns[$alias])) {
				throw new Exception('Non unique alias name: ' . $alias);
			}
		}

		if ($column === '*') {
			if ($alias !== null) {
				throw new Exception("Can't set alias to '*'");
			}

			if (isset($this->columns['*'])) {
				throw new Exception("Multiple definition of '*'");
			}

			$alias = '*';

			$e = new PlainSql('*');

		} elseif ($column instanceof Literal) {
			$e = $column;

		} elseif (is_string($column)) {
			$e = new Identifier($column);

		} else {
			throw new Exception('Column name or Literal expected');
		}

		if ($alias !== null) {
			$this->columns[$alias] = $e;
		} else {
			$this->columns[] = $e;
		}
	}
}

class D2
{

	public function select($table)
	{
		return new Select($table);
	}

	public function update($table)
	{
		$u = new Update($table);

		return $u;
	}

	public function delete($table)
	{
		$d = new Delete($table);

		return $d;
	}

	public function insert($table, array $rows = null)
	{
		$insert = new Insert($table);

		if ($rows)
			$insert->values($rows);

		return $insert;
	}
}

//
//$quote = new Quote;
//$d2 = new D2;
//$insert = new Insert('sss.aaa');
//$insert->ignore();
//$insert->row(['a' => new BiOperation(new Identifier('field'), '=', new Constant(1000))]);
//$insert->onDuplicateKeyUpdate();
//
//echo $insert->toString($quote), "\n";
//
//$where = new Where();
//$where->addAnd(new BiOperation(new Identifier('column'), '=', new Constant('column')));
//$where->addAnd(new NotIn(new Identifier('column'), [new Constant('v0'), new Constant('v1')]));
//echo $where->toString($quote), "\n";
