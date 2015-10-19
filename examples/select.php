<?php
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/../tests/mock/Quote.php';
$quote = new b2\tests\mock\Quote();

echo $b2->select('user', 'id > ?', [10])->column('uid')->toString($quote);
