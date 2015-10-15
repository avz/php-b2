<?php
namespace common\d2\tests;

class WhereTest extends Base
{
	public function testToString() {
		$where = new \common\d2\Where(new \common\d2\Identifier('hello'));
		$this->assertEquals('`hello`', $where->toString($this->quoter()));

		$where = new \common\d2\Where;
		$where->addAnd(new \common\d2\Identifier('column'));
		$this->assertEquals('`column`', $where->toString($this->quoter()));

		$where->addAnd(new \common\d2\Identifier('column2'));
		$this->assertEquals('(`column` AND `column2`)', $where->toString($this->quoter()));

		$where->addOr(new \common\d2\Identifier('column3'));
		$this->assertEquals('((`column` AND `column2`) OR `column3`)', $where->toString($this->quoter()));

		$where = new \common\d2\Where;
		$where->addOr(new \common\d2\Constant(10));
		$this->assertEquals("'10'", $where->toString($this->quoter()));
	}

	public function testIsEmpty() {
		$where = new \common\d2\Where(new \common\d2\Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());

		$where = new \common\d2\Where();
		$this->assertEquals(true, $where->isEmpty());

		$where->addAnd(new \common\d2\Identifier('hello'));
		$this->assertEquals(false, $where->isEmpty());
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Empty WHERE
	 */
	public function testEmpty() {
		$where = new \common\d2\Where;
		$where->toString($this->quoter());
	}
}
