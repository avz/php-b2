<?php
namespace d2\tests;

use d2\literal\Constant;
use d2\literal\Call;
use d2\literal\Identifier;

class CallTest extends Base
{
	public function testToString() {
		$this->assertEquals("HELLO()", (new Call('HELLO'))->toString($this->quoter()));
		$this->assertEquals("HELLO1('1')", (new Call('HELLO1', [new Constant(1)]))->toString($this->quoter()));
		$this->assertEquals("HELLO2('1', '2')", (new Call('HELLO2', [new Constant(1), new Constant(2)]))->toString($this->quoter()));
		$this->assertEquals("HELLO3('1', '2', '3')", (new Call('HELLO3', [new Constant(1), new Constant(2), new Constant(3)]))->toString($this->quoter()));
	}
}
