<?php
namespace common\d2\tests;

class IdentifierTest extends Base
{
	public function testToString() {
		$this->assertEquals('`column`', (new \common\d2\Identifier('column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`', (new \common\d2\Identifier('table.column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`.`sub`', (new \common\d2\Identifier('table.column.sub'))->toString($this->quoter()));
	}

	/**
	 * @expectedException common\d2\Exception
	 * @expectedExceptionMessage Identifier must be a string
	 */
	public function testInvalidArgument() {
		new \common\d2\Identifier(10);
	}
}
