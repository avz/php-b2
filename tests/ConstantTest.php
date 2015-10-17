<?php
namespace d2\tests;

use d2\literal\Constant;

class ConstantTest extends Base
{
	public function testToString() {
		$this->assertEquals("'10'", (new Constant(10))->toString($this->quoter()));
		$this->assertEquals("'helo'", (new Constant('helo'))->toString($this->quoter()));
		$this->assertEquals("NULL", (new Constant(null))->toString($this->quoter()));
		$this->assertEquals("1", (new Constant(true))->toString($this->quoter()));
		$this->assertEquals("0", (new Constant(false))->toString($this->quoter()));
	}

	/**
	 * @expectedException \d2\Exception
	 * @expectExceptionMessage Objects is not allowed
	 */
	public function testInvalidArgument() {
		new Constant(new \stdClass);
	}
}
