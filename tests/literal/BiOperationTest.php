<?php
namespace d2\tests\literal;

use d2\literal\BiOperation;
use d2\literal\Constant;
use d2\literal\Identifier;

class BiOperationTest extends \d2\tests\Base
{
	public function testToString() {
		$operand1 = new Constant(10);
		$operand2 = new Identifier('hello');

		$o = new BiOperation($operand1, 'AND', $operand2);
		$this->assertEquals("'10' AND `hello`", $o->toString($this->quoter()));

		$o = new BiOperation($operand2, '=', $operand1);
		$this->assertEquals("`hello` = '10'", $o->toString($this->quoter()));

		$o = new BiOperation($operand2, '=', $operand2);
		$this->assertEquals("`hello` = `hello`", $o->toString($this->quoter()));

		$operand3 = new BiOperation(new Identifier('sub'), '=', new Constant('val'));
		$o = new BiOperation($operand2, 'AND', $operand3);
		$this->assertEquals("`hello` AND (`sub` = 'val')", $o->toString($this->quoter()));
	}
}
