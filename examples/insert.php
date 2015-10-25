<?php
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/../tests/mock/Quote.php';
$quote = new b2\tests\mock\Quote();

$b2 = new b2\B2;

echo $b2->insert('user', [['id' => 1, 'name' => 'John']])->toString($quote) . "\n";
echo $b2->insert('user')->row(['id' => 1, 'name' => 'John'])->toString($quote) . "\n";
echo $b2->insert('user')->rows([['id' => 1, 'name' => 'John']])->toString($quote) . "\n";

echo $b2->insert('user', [
	['id' => 1, 'name' => 'John'],
	['id' => 2, 'name' => 'Ivan']
])->toString($quote) . "\n";
// or
echo $b2->insert('user')
	->row(['id' => 1, 'name' => 'John'])
	->row(['id' => 2, 'name' => 'Ivan'])
->toString($quote) . "\n";
// or
echo $b2->insert('user')
	->rows([
		['id' => 1, 'name' => 'John']
		, ['id' => 2, 'name' => 'Ivan']
	])
->toString($quote) . "\n";

echo $b2->insert('user', [['id' => 1, 'name' => 'John']])
	->onDuplicateKeyUpdate()
->toString($quote) . "\n";

echo $b2->insert('user', [['id' => 1, 'name' => 'John', 'age' => 20]])
	->onDuplicateKeyUpdate('name')
	->onDuplicateKeyUpdate('age')
->toString($quote) . "\n";


echo $b2->insert('user', [['id' => 1, 'name' => 'John', 'age' => 20]])
	->onDuplicateKeyUpdate(['name', 'age'])
->toString($quote) . "\n";


echo $b2->insert('counter')->row(['name' => 'visits', 'count' => 1])
	->onDuplicateKeyUpdate([
		'count' => $b2->sql('`count` + VALUES(`count`)')
	])
->toString($quote) . "\n";
