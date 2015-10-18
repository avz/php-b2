<?php
namespace b2\tests\literal;

use b2\literal\In;
use b2\literal\Constant;
use b2\literal\Identifier;

class InTest extends \b2\tests\Base
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
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testEmptyException() {
		$in = new In(new Identifier('col'));

		$in->toString($this->quoter());
	}
}
