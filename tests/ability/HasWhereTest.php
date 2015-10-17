<?php
namespace d2\tests\ability;

use d2\literal\PlainSql;
use d2\literal\Constant;
use d2\literal\Identifier;
use d2\literal\Where;

class SimpleWhereable
{
	use \d2\ability\HasWhere;

	public function __construct()
	{
		$this->where = new Where;
	}

	public function toString(\d2\Quote $q)
	{
		return $this->whereToString($q);
	}

}

class HasWhereTest extends \d2\tests\Base
{

	public function testColumnValue()
	{
		$q = new SimpleWhereable();
		$q->where('column', 'value');

		$this->assertEquals("(`column` = 'value')", $q->toString($this->quoter()));
		$q->where('column2', 'value2');
		$this->assertEquals("((`column` = 'value') AND (`column2` = 'value2'))", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', 10);
		$this->assertEquals("(`column` = '10')", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', new Identifier('column2'));
		$this->assertEquals("(`column` = `column2`)", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column', new PlainSql('NOW()'));
		$this->assertEquals("(`column` = NOW())", $q->toString($this->quoter()));
	}

	public function testSql()
	{
		$q = new SimpleWhereable();
		$q->where('column > 21');

		$this->assertEquals("(column > 21)", $q->toString($this->quoter()));

		$q->where('NOW() = @doomsday');

		$this->assertEquals("((column > 21) AND (NOW() = @doomsday))", $q->toString($this->quoter()));
	}

	public function testBinds() {
		$q = new SimpleWhereable();
		$q->where('column > ?', [1]);

		$this->assertEquals("(column > '1')", $q->toString($this->quoter()));

		$q->where('NOW() = place');

		$this->assertEquals("((column > '1') AND (NOW() = place))", $q->toString($this->quoter()));
	}

	public function testColumnIn()
	{
		$q = new SimpleWhereable();
		$q->where('column', ['value1']);
		$this->assertEquals("`column` IN('value1')", $q->toString($this->quoter()));

		$q->where('column2', ['value2']);
		$this->assertEquals("(`column` IN('value1') AND `column2` IN('value2'))", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('column2', [new PlainSql("NOW()"), new PlainSql("TODAY()")]);
		$this->assertEquals("`column2` IN(NOW(), TODAY())", $q->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testColumnInEmpty()
	{
		$q = new SimpleWhereable();
		$q->where('column', []);
		$q->toString($this->quoter());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Object is not allowed
	 */
	public function testInvalidArgumentException()
	{
		$q = new SimpleWhereable();
		$q->where('column', new \stdClass);
	}

}
