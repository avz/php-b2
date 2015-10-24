<?php
namespace b2\tests;

class QuoteTest extends \b2\tests\Base
{
	public function testCreate()
	{
		$mysqli = new \mysqli;
		$quote = \b2\Quote::createFromMysqli($mysqli);
		$this->assertInstanceOf(\b2\quote\Mysqli::class, $quote);
	}

	public function testQuoteMysqli()
	{
		$mysqli = $this->getMockBuilder('mysqli')
			->setMethods(array('escape_string'))
			->getMock()
		;

		$mysqli->expects($this->once())
			->method('escape_string')
			->with($this->equalTo('hello world'))
			->will($this->returnValue('{hello world}'))
		;

		$quote = \b2\Quote::createFromMysqli($mysqli);
		$this->assertSame("'{hello world}'", $quote->value('hello world'));

		$this->assertSame('NULL', $quote->value(null));
		$this->assertSame('1', $quote->value(true));
		$this->assertSame('0', $quote->value(false));

		$this->assertSame('`hello`.`world`', $quote->identifier('hello.world'));
	}

	public function testLiteral()
	{
		$this->assertSame('plain SQL', $this->quoter()->value(new \b2\literal\PlainSql('plain SQL')));
		$this->assertSame('col1 + col2', $this->quoter()->identifier(new \b2\literal\PlainSql('col1 + col2')));
	}

	public function testValueList()
	{
		$this->assertSame("'hello', 'world'", $this->quoter()->value(['hello', 'world']));
	}
}
