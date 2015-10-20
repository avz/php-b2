<?php
namespace b2\tests\literal;

use b2\literal\Where;
use b2\literal\Identifier;
use b2\literal\Constant;

class WhereTest extends \b2\tests\Base
{
	public function testToString() {
		$where = new Where(new Identifier('hello'));
		$this->assertSame('`hello`', $where->toString($this->quoter()));

		$where = new Where;
		$where->addAnd(new Identifier('column'));
		$this->assertSame('`column`', $where->toString($this->quoter()));

		$where->addAnd(new Identifier('column2'));
		$this->assertSame('`column` AND `column2`', $where->toString($this->quoter()));

		$where->addOr(new Identifier('column3'));
		$this->assertSame('(`column` AND `column2`) OR `column3`', $where->toString($this->quoter()));

		$where = new Where;
		$where->addOr(new Constant(10));
		$this->assertSame("'10'", $where->toString($this->quoter()));
	}

	public function testIsEmpty() {
		$where = new Where(new Identifier('hello'));
		$this->assertSame(false, $where->isEmpty());

		$where = new Where();
		$this->assertSame(true, $where->isEmpty());

		$where->addAnd(new Identifier('hello'));
		$this->assertSame(false, $where->isEmpty());
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Empty WHERE
	 */
	public function testEmpty() {
		$where = new Where;
		$where->toString($this->quoter());
	}
}
