<?php
namespace b2\tests\ability;

use b2\literal\PlainSql;
use b2\literal\Constant;
use b2\literal\Identifier;
use b2\literal\Where;

class SimpleWhereable
{
	use \b2\ability\HasWhere;

	public function __construct()
	{
		$this->where = new Where;
	}

	public function toString(\b2\Quote $q)
	{
		return $this->whereToString($q);
	}

}

class HasWhereTest extends \b2\tests\Base
{

	public function testColumnValue()
	{
		$q = new SimpleWhereable();
		$q->where('column', 'value');

		$this->assertSame("`column` = 'value'", $q->toString($this->quoter()));
		$q->where('column2', 'value2');
		$this->assertSame("(`column` = 'value') AND (`column2` = 'value2')", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', 10);
		$this->assertSame("`column` = '10'", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', new Identifier('column2'));
		$this->assertSame("`column` = `column2`", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', new PlainSql('NOW()'));
		$this->assertSame("`column` = (NOW())", $q->toString($this->quoter()));
	}

	public function testSql()
	{
		$q = new SimpleWhereable();
		$q->where('column > 21');

		$this->assertSame("column > 21", $q->toString($this->quoter()));

		$q->where('NOW() = @doomsday');

		$this->assertSame("(column > 21) AND (NOW() = @doomsday)", $q->toString($this->quoter()));
	}

	public function testBinds() {
		$q = new SimpleWhereable();
		$q->where('column > ?', [1]);

		$this->assertSame("column > '1'", $q->toString($this->quoter()));

		$q->where('NOW() = place');

		$this->assertSame("(column > '1') AND (NOW() = place)", $q->toString($this->quoter()));
	}

	public function testColumnIn()
	{
		$q = new SimpleWhereable();
		$q->where('column', ['value1']);
		$this->assertSame("`column` IN('value1')", $q->toString($this->quoter()));

		$q->where('column2', ['value2']);
		$this->assertSame("`column` IN('value1') AND `column2` IN('value2')", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column2', [new PlainSql("NOW()"), new PlainSql("TODAY()")]);
		$this->assertSame("`column2` IN(NOW(), TODAY())", $q->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testColumnInEmpty()
	{
		$q = new SimpleWhereable();
		$q->where('column', []);
		$q->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Object is not allowed
	 */
	public function testInvalidArgumentException()
	{
		$q = new SimpleWhereable();
		$q->where('column', new \stdClass);
	}

}
