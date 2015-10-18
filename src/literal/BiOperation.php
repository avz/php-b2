<?php
namespace d2\literal;

class BiOperation extends \d2\Literal
{
	public $nodes = [];
	public $left;
	public $right;
	public $operator;

	public function __construct(\d2\Literal $left, $operator, \d2\Literal $right)
	{
		$this->left = $left;
		$this->right = $right;
		$this->operator = $operator;
	}

	public function toString(\d2\Quote $quote)
	{
		$left = $this->left->toString($quote);

		if ($this->left instanceof BiOperation || $this->left instanceof PlainSql)
			$left = "($left)";

		$right = $this->right->toString($quote);

		if ($this->right instanceof BiOperation || $this->right instanceof PlainSql)
			$right = "($right)";

		return $left . ' ' . $this->operator . ' ' . $right;
		;
	}

}
