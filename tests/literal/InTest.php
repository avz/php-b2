<?php
namespace d2\tests\literal;

use d2\literal\In;
use d2\literal\Constant;
use d2\literal\Identifier;

class InTest extends \d2\tests\Base
{
	public function testToString() {
		$in = new In(new Identifier('c'));
		$in->addCase(new Constant('hi'));

		$this->assertEquals("`c` IN('hi')", $in->toString($this->quoter()));

		$in->addCase(new Constant('wo'));

		$this->assertEquals("`c` IN('hi', 'wo')", $in->toString($this->quoter()));

		$in = new In(new Identifier('c'), [new Constant('wo')]);
		$this->assertEquals("`c` IN('wo')", $in->toString($this->quoter()));

		$in = new In(new Identifier('c'), [new Constant('hi'), new Constant('wo')]);
		$this->assertEquals("`c` IN('hi', 'wo')", $in->toString($this->quoter()));
		$in->addCase(new Identifier('col'));
		$this->assertEquals("`c` IN('hi', 'wo', `col`)", $in->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testEmptyException() {
		$in = new In(new Identifier('col'));

		$in->toString($this->quoter());
	}
}
