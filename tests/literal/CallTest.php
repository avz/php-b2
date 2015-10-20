<?php
namespace b2\tests\literal;

use b2\literal\Constant;
use b2\literal\Call;
use b2\literal\Identifier;

class CallTest extends \b2\tests\Base
{
	public function testToString() {
		$this->assertSame("HELLO()", (new Call('HELLO'))->toString($this->quoter()));
		$this->assertSame("HELLO1('1')", (new Call('HELLO1', [new Constant(1)]))->toString($this->quoter()));
		$this->assertSame("HELLO2('1', '2')", (new Call('HELLO2', [new Constant(1), new Constant(2)]))->toString($this->quoter()));
		$this->assertSame("HELLO3('1', '2', '3')", (new Call('HELLO3', [new Constant(1), new Constant(2), new Constant(3)]))->toString($this->quoter()));
	}
}
