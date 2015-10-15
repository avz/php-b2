<?php

namespace common\d2;

class Exception extends \Exception
{

}

class Quote {
	public function value($any) {
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

	public function identifier($any) {
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

	protected function quote($any) {

	}
}

class PlainSql extends Literal
{
	private $sql;
	private $binds = [];

	public function __construct($sql, array $binds = [])
	{
		if (!is_string($sql))
			throw new Exception('SQL must be a string');

		$this->sql = $sql;
		$this->binds = $binds;
	}

	public function toString(Quote $quote)
	{
		$n = 0;

		$usedKeys = [];

		return preg_replace_callback(
			'/\?|:([a-z0-9_]+)/',
			function($w) use(&$n, &$usedKeys) {
				$v = null;

				if ($w[0] === '?') {
					if (!array_key_exists($n, $this->binds)) {
						throw new Exception("Bind offset $n was not found");
					}

					$usedKeys[] = $n;
					$n++;
				} else {
					$usedKeys[] = $w[1];
				}
			},
			$this->sql
		);
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
		if(is_object($constant))
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

class Where extends Literal {
	private $expression = null;

	public function __construct(Literal $expression = null) {
		$this->expression = $expression;
	}

	public function addAnd(Literal $expression) {
		return $this->addToEnd('AND', $expression);
	}

	public function addOr(Literal $expression) {
		return $this->addToEnd('OR', $expression);
	}

	private function addToEnd($operator, Literal $expression) {
		if ($this->expression) {
			$this->expression = new BiOperation($this->expression, $operator, $expression);
		} else {
			$this->expression = $expression;
		}
	}

	public function isEmpty() {
		return !$this->expression;
	}

	public function toString(Quote $quote) {
		if (!$this->expression)
			throw new Exception('Empty WHERE');

		return $this->expression->toString($quote);
	}
}

class In extends Literal {
	private $expression;
	private $cases = [];

	public function __construct(Literal $expression, array $cases = [])
	{
		$this->expression = $expression;
		$this->cases = $cases;
	}

	public function addCase(Literal $case) {
		$this->cases[] = $case;
	}

	public function toString(Quote $quote) {
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

class Delete extends Query
{
	private $where;

	public function toString(Quote $quote)
	{

	}

	public function where()
	{

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

	}

	public function insertInto($tableName, array $rows = null)
	{
		return new Insert($this, $tableName);
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
