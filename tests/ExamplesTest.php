<?php
namespace b2\tests;

class ExamplesTest extends \b2\tests\Base
{
	public function testSelect()
	{
		$this->expectOutputString(<<<EOF
SELECT `uid`, `name`, `xp` FROM `user` WHERE id > '10'
SELECT SUM(payment.price * '10') AS `sum` FROM `user` LEFT JOIN `payment` ON payment.id = user.id WHERE id = '10'

EOF
		);
		require_once __DIR__ . '/../examples/select.php';
	}

	public function testUpdate()
	{
		$this->expectOutputString(<<<EOF
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = '10' WHERE `id` = '1'
UPDATE `user` SET `money` = `money` - '20', `vip` = '1', `bannedUntil` = NULL, field = '10' WHERE (`id` = '2') AND (`id` OR `uid`)

EOF
		);
		require_once __DIR__ . '/../examples/update.php';
	}
}
