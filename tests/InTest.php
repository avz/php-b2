<?php
namespace common\d2\tests;

class InTest extends Base
{
	public function testToString() {
		$in = new \common\d2\In(new \common\d2\Identifier('c'));
		$in->addCase(new \common\d2\Constant('hi'));

		$this->assertEquals("`c` IN('hi')", $in->toString($this->quoter()));

		$in->addCase(new \common\d2\Constant('wo'));

		$this->assertEquals("`c` IN('hi', 'wo')", $in->toString($this->quoter()));

		$in = new \common\d2\In(new \common\d2\Identifier('c'), [new \common\d2\Constant('wo')]);
		$this->assertEquals("`c` IN('wo')", $in->toString($this->quoter()));

		$in = new \common\d2\In(new \common\d2\Identifier('c'), [new \common\d2\Constant('hi'), new \common\d2\Constant('wo')]);
		$this->assertEquals("`c` IN('hi', 'wo')", $in->toString($this->quoter()));
		$in->addCase(new \common\d2\Identifier('col'));
		$this->assertEquals("`c` IN('hi', 'wo', `col`)", $in->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage IN is empty
	 */
	public function testEmptyException() {
		$in = new \common\d2\In(new \common\d2\Identifier('col'));

		$in->toString($this->quoter());
	}
}
