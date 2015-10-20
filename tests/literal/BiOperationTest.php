<?php
namespace b2\tests\literal;

use b2\literal\BiOperation;
use b2\literal\Constant;
use b2\literal\Identifier;

class BiOperationTest extends \b2\tests\Base
{
	public function testToString() {
		$operand1 = new Constant(10);
		$operanb2 = new Identifier('hello');

		$o = new BiOperation($operand1, 'AND', $operanb2);
		$this->assertSame("'10' AND `hello`", $o->toString($this->quoter()));

		$o = new BiOperation($operanb2, '=', $operand1);
		$this->assertSame("`hello` = '10'", $o->toString($this->quoter()));

		$o = new BiOperation($operanb2, '=', $operanb2);
		$this->assertSame("`hello` = `hello`", $o->toString($this->quoter()));

		$operand3 = new BiOperation(new Identifier('sub'), '=', new Constant('val'));
		$o = new BiOperation($operanb2, 'AND', $operand3);
		$this->assertSame("`hello` AND (`sub` = 'val')", $o->toString($this->quoter()));
	}
}
