<?php
namespace b2\tests;

class Base extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 * @return \b2\Quote
	 */
	public function quoter()
	{
		return new mock\Quote();
	}
}
