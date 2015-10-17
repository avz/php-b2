<?php
namespace d2\tests\literal;

use d2\literal\Where;
use d2\literal\Identifier;
use d2\literal\Constant;

class WhereTest extends \d2\tests\Base
{
	public function testToString() {
		$where = new Where(new Identifier('hello'));
		$this->assertEquals('`hello`', $where->toString($this->quoter()));

		$where = new Where;
		$where->addAnd(new Identifier('column'));
		$this->assertEquals('`column`', $where->toString($this->quoter()));

		$where->addAnd(new Identifier('column2'));
		$this->assertEquals('(`column` AND `column2`)', $where->toString($this->quoter()));

		$where->addOr(new Identifier('column3'));
		$this->assertEquals('((`column` AND `column2`) OR `column3`)', $where->toString($this->quoter()));

		$where = new Where;
		$where->addOr(new Constant(10));
		$this->assertEquals("'10'", $where->toString($this->quoter()));
	}

	public function testIsEmpty() {
		$where = new Where(new Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());

		$where = new Where();
		$this->assertEquals(true, $where->isEmpty());

		$where->addAnd(new Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Empty WHERE
	 */
	public function testEmpty() {
		$where = new Where;
		$where->toString($this->quoter());
	}
}
