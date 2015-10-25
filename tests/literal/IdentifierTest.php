<?php
namespace b2\tests\literal;

use b2\literal\Identifier;

class IdentifierTest extends \b2\tests\Base
{
	public function testToString() {
		$this->assertSame('`field`', (new Identifier('field'))->toString($this->quoter()));
		$this->assertSame('`table`.`field`', (new Identifier('table.field'))->toString($this->quoter()));
		$this->assertSame('`table`.`field`.`sub`', (new Identifier('table.field.sub'))->toString($this->quoter()));
	}

	/**
	 * @expectedException b2\Exception
	 * @expectedExceptionMessage Identifier must be a string
	 */
	public function testInvalidArgument() {
		new Identifier(10);
	}
}
