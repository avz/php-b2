<?php
namespace common\d2\tests;

class BiOperationTest extends Base
{
	public function testToString() {
		$operand1 = new \common\d2\Constant(10);
		$operand2 = new \common\d2\Identifier('hello');

		$o = new \common\d2\BiOperation($operand1, 'AND', $operand2);
		$this->assertEquals("('10' AND `hello`)", $o->toString($this->quoter()));

		$o = new \common\d2\BiOperation($operand2, '=', $operand1);
		$this->assertEquals("(`hello` = '10')", $o->toString($this->quoter()));

		$o = new \common\d2\BiOperation($operand2, '=', $operand2);
		$this->assertEquals("(`hello` = `hello`)", $o->toString($this->quoter()));

		$operand3 = new \common\d2\BiOperation(new \common\d2\Identifier('sub'), '=', new \common\d2\Constant('val'));
		$o = new \common\d2\BiOperation($operand2, 'AND', $operand3);
		$this->assertEquals("(`hello` AND (`sub` = 'val'))", $o->toString($this->quoter()));
	}
}
