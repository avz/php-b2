<?php
namespace b2\tests\literal;

use b2\literal\Identifier;

class IdentifierTest extends \b2\tests\Base
{
	public function testToString() {
		$this->assertEquals('`column`', (new Identifier('column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`', (new Identifier('table.column'))->toString($this->quoter()));
		$this->assertEquals('`table`.`column`.`sub`', (new Identifier('table.column.sub'))->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Identifier must be a string
	 */
	public function testInvalidArgument() {
		new Identifier(10);
	}
}