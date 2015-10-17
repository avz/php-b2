<?php
namespace d2\tests;

class IdentifierTest extends Base
{
	public function testToString() {
		$this->assertEquals('`column`', (new \d2\Identifier('column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`', (new \d2\Identifier('table.column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`.`sub`', (new \d2\Identifier('table.column.sub'))->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Identifier must be a string
	 */
	public function testInvalidArgument() {
		new \d2\Identifier(10);
	}
}
