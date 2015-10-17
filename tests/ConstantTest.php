<?php
namespace d2\tests;

class ConstantTest extends Base
{
	public function testToString() {
		$this->assertEquals("'10'", (new \d2\Constant(10))->toString($this->quoter()));
		$this->assertEquals("'helo'", (new \d2\Constant('helo'))->toString($this->quoter()));
		$this->assertEquals("NULL", (new \d2\Constant(null))->toString($this->quoter()));
		$this->assertEquals("1", (new \d2\Constant(true))->toString($this->quoter()));
		$this->assertEquals("0", (new \d2\Constant(false))->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectExceptionMessage Objects is not allowed
	 */
	public function testInvalidArgument() {
		new \d2\Constant(new \stdClass);
	}
}
