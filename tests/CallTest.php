<?php
namespace d2\tests;

class CallTest extends Base
{
	public function testToString() {
		$this->assertEquals("HELLO()", (new \d2\Call('HELLO'))->toString($this->quoter()));
		$this->assertEquals("HELLO1('1')", (new \d2\Call('HELLO1', [new \d2\Constant(1)]))->toString($this->quoter()));
		$this->assertEquals("HELLO2('1', '2')", (new \d2\Call('HELLO2', [new \d2\Constant(1), new \d2\Constant(2)]))->toString($this->quoter()));
		$this->assertEquals("HELLO3('1', '2', '3')", (new \d2\Call('HELLO3', [new \d2\Constant(1), new \d2\Constant(2), new \d2\Constant(3)]))->toString($this->quoter()));
	}
}
