<?php
namespace common\d2\tests;

class Base extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 * @return \common\d2\Quote
	 */
	public function quoter() {
		return new mock\Quote();
	}
}
