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

	public function testFieldValue()
	{
		$q = new SimpleWhereable();
		$q->where('field', 'value');

		$this->assertSame("`field` = 'value'", $q->toString($this->quoter()));
		$q->where('field2', 'value2');
		$this->assertSame("(`field` = 'value') AND (`field2` = 'value2')", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('field', 10);
		$this->assertSame("`field` = '10'", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('field', new Identifier('field2'));
		$this->assertSame("`field` = `field2`", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('field', new PlainSql('NOW()'));
		$this->assertSame("`field` = (NOW())", $q->toString($this->quoter()));
	}

	public function testSql()
	{
		$q = new SimpleWhereable();
		$q->where('field > 21');

		$this->assertSame("field > 21", $q->toString($this->quoter()));

		$q->where('NOW() = @doomsday');

		$this->assertSame("(field > 21) AND (NOW() = @doomsday)", $q->toString($this->quoter()));
	}

	public function testBinds() {
		$q = new SimpleWhereable();
		$q->where('field > ?', [1]);

		$this->assertSame("field > '1'", $q->toString($this->quoter()));

		$q->where('NOW() = place');

		$this->assertSame("(field > '1') AND (NOW() = place)", $q->toString($this->quoter()));
	}

	public function testfieldIn()
	{
		$q = new SimpleWhereable();
		$q->where('field', ['value1']);
		$this->assertSame("`field` IN('value1')", $q->toString($this->quoter()));

		$q->where('field2', ['value2']);
		$this->assertSame("`field` IN('value1') AND `field2` IN('value2')", $q->toString($this->quoter()));

		$q = new SimpleWhereable();
		$q->where('field2', [new PlainSql("NOW()"), new PlainSql("TODAY()")]);
		$this->assertSame("`field2` IN(NOW(), TODAY())", $q->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testfieldInEmpty()
	{
		$q = new SimpleWhereable();
		$q->where('field', []);
		$q->toString($this->quoter());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Object is not allowed
	 */
	public function testInvalidArgumentException()
	{
		$q = new SimpleWhereable();
		$q->where('field', new \stdClass);
	}

}
