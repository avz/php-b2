<?php
namespace d2\tests;

use d2\literal\Identifier;

class IdentifierTest extends Base
{
	public function testToString() {
		$this->assertEquals('`column`', (new Identifier('column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`', (new Identifier('table.column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`.`sub`', (new Identifier('table.column.sub'))->toString($this->quoter()));
	}

	/**
	 * @expectedException d2\Exception
	 * @expectedExceptionMessage Identifier must be a string
	 */
	public function testInvalidArgument() {
		new Identifier(10);
	}
}
