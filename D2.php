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
	protected $tableName;

	public function __construct($tableName)
	{
		$this->tableName = $tableName;
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

		$sql .= ' INTO ' . $quote->identifier($this->tableName);

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

trait Whereable
{
	/**
	 *
	 * @var Where
	 */
	private $where;

	private function wherePrepared($prepared, array $binds = [])
	{
		$binds = array_map(
			function ($value) {
			return new Constant($value);
		}, $binds
		);

		return new PlainSql($prepared, $binds);
	}

	private function whereFieldEqual($columnName, $columnValue)
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

	public function where($fieldNameOrPrepared, $valueOrBinds = null/* ... */)
	{
		$where = clone $this->where;

		if (is_string($fieldNameOrPrepared)) {
			if (PlainSql::hasPlaceholders($fieldNameOrPrepared) || func_num_args() === 1) {
				if (func_num_args() == 1) {
					$valueOrBinds = [];
				} else {
					if (!is_array($valueOrBinds))
						throw new Exception('Binds must be an array');
				}

				$where->addAnd($this->wherePrepared('(' . $fieldNameOrPrepared . ')', $valueOrBinds));
			} else {
				if (func_num_args() !== 2)
					throw new Exception('Exactly 2 arguments expected');

				$where->addAnd($this->whereFieldEqual($fieldNameOrPrepared, $valueOrBinds));
			}
		} else if ($fieldNameOrPrepared instanceof Literal) {
			$where->addAnd($fieldNameOrPrepared);
		} else if (is_array($fieldNameOrPrepared)) {
			foreach ($fieldNameOrPrepared as $column => $value) {
				$where->addAnd($this->whereFieldEqual($column, $value));
			}
		} else {
			throw new Exception('Incorrect WHERE definition');
		}

		$this->where = $where;

		return $this;
	}

	protected function whereIsEmpty()
	{
		return $this->where->isEmpty();
	}

	protected function whereToString(Quote $quote)
	{
		return $this->where->toString($quote);
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

	protected function limitToString(Quote $quote)
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

	public function orderIsEmpty()
	{
		return !$this->orders;
	}

	public function orderToString(Quote $quote)
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

}

class Delete extends Query
{

	use Whereable;
	use Orderable;
	use Limitable;

	/**
	 *
	 * @var Literal
	 */
	private $table;

	public function __construct($table)
	{
		if (is_string($table)) {
			$this->table = new Identifier($table);
		} elseif ($table instanceof Literal) {
			$this->table = $table;
		} else {
			throw new Exception('Only strings and Literals allowed in table name');
		}

		$this->where = new Where;
	}

	public function toString(Quote $quote)
	{
		$sql = 'DELETE FROM ' . $this->table->toString($quote);
		if (!$this->whereIsEmpty()) {
			$sql .= ' WHERE ' . $this->whereToString($quote);
		}

		if (!$this->orderIsEmpty()) {
			$sql .= ' ' . $this->orderToString($quote);
		}

		if (!$this->limitIsEmpty()) {
			$sql .= ' ' . $this->limitToString($quote);
		}

		return $sql;
	}

}

class D2
{

	public function selectFrom($tableName)
	{

	}

	public function update($tableName)
	{

	}

	public function deleteFrom($tableName)
	{
		return new Delete($tableName);
	}

	public function insertInto($tableName, array $rows = null)
	{
		$insert = new Insert($tableName);

		if ($rows)
			$insert->row($rows);

		return $insert;
	}

	public function quote($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'quote'], $any));
		} elseif (is_null($any)) {
			return 'NULL';
		} else {
			return "'" . addslashes($any) . "'";
		}
	}

	public function quoteIdentifier($any)
	{
		if (is_array($any)) {
			return implode(', ', array_map([$this, 'quoteIdentifier'], $any));
		} else {
			return '`' . str_replace('.', '`.`', (string)$any) . '`';
		}
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
