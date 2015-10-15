<?php
namespace common\d2\tests;

class ConstantTest extends Base
{
	public function testToString() {
		$this->assertEquals("'10'", (new \common\d2\Constant(10))->toString($this->quoter()));
		$this->assertEquals("'helo'", (new \common\d2\Constant('helo'))->toString($this->quoter()));
		$this->assertEquals("NULL", (new \common\d2\Constant(null))->toString($this->quoter()));
		$this->assertEquals("1", (new \common\d2\Constant(true))->toString($this->quoter()));
		$this->assertEquals("0", (new \common\d2\Constant(false))->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectExceptionMessage Objects is not allowed
	 */
	public function testInvalidArgument() {
		new \common\d2\Constant(new \stdClass);
	}
}
