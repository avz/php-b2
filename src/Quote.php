<?php
namespace d2;

abstract class Quote
{
	abstract public function value($any);
	abstract public function identifier($any);
}
