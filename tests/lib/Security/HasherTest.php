<?php
/**
 * Copyright (c) 2014 Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Security;

use OC\Security\Hasher;
use OCP\IConfig;

/**
 * Class HasherTest
 */
class HasherTest extends \Test\TestCase {

	/**
	 * @return array
	 */
	public function versionHashProvider()
	{
		return [
			['asf32äà$$a.|3', null],
			['asf32äà$$a.|3|5', null],
			['1|2|3|4', ['version' => 1, 'hash' => '2|3|4']],
			['1|我看|这本书。 我看這本書', ['version' => 1, 'hash' => '我看|这本书。 我看這本書']],
			['2|newhash', ['version' => 2, 'hash' => 'newhash']],
		];
	}

	/**
	 * @return array
	 */
	public function hashProviders70_71()
	{
		return [
			// Valid SHA1 strings
			['password', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', true],
			['owncloud.com', '27a4643e43046c3569e33b68c1a4b15d31306d29', true],

			// Invalid SHA1 strings
			['InvalidString', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', false],
			['AnotherInvalidOne', '27a4643e43046c3569e33b68c1a4b15d31306d29', false],

			// Valid legacy password string with password salt "6Wow67q1wZQZpUUeI6G2LsWUu4XKx"
			['password', '$2a$08$emCpDEl.V.QwPWt5gPrqrOhdpH6ailBmkj2Hd2vD5U8qIy20HBe7.', true],
			['password', '$2a$08$yjaLO4ev70SaOsWZ9gRS3eRSEpHVsmSWTdTms1949mylxJ279hzo2', true],
			['password', '$2a$08$.jNRG/oB4r7gHJhAyb.mDupNUAqTnBIW/tWBqFobaYflKXiFeG0A6', true],
			['owncloud.com', '$2a$08$YbEsyASX/hXVNMv8hXQo7ezreN17T8Jl6PjecGZvpX.Ayz2aUyaZ2', true],
			['owncloud.com', '$2a$11$cHdDA2IkUP28oNGBwlL7jO/U3dpr8/0LIjTZmE8dMPA7OCUQsSTqS', true],
			['owncloud.com', '$2a$08$GH.UoIfJ1e.qeZ85KPqzQe6NR8XWRgJXWIUeE1o/j1xndvyTA1x96', true],

			// Invalid legacy passwords
			['password', '$2a$08$oKAQY5IhnZocP.61MwP7xu7TNeOb7Ostvk3j6UpacvaNMs.xRj7O2', false],

			// Valid passwords "6Wow67q1wZQZpUUeI6G2LsWUu4XKx"
			['password', '1|$2a$05$ezAE0dkwk57jlfo6z5Pql.gcIK3ReXT15W7ITNxVS0ksfhO/4E4Kq', true],
			['password', '1|$2a$05$4OQmloFW4yTVez2MEWGIleDO9Z5G9tWBXxn1vddogmKBQq/Mq93pe', true],
			['password', '1|$2a$11$yj0hlp6qR32G9exGEXktB.yW2rgt2maRBbPgi3EyxcDwKrD14x/WO', true],
			['owncloud.com', '1|$2a$10$Yiss2WVOqGakxuuqySv5UeOKpF8d8KmNjuAPcBMiRJGizJXjA2bKm', true],
			['owncloud.com', '1|$2a$10$v9mh8/.mF/Ut9jZ7pRnpkuac3bdFCnc4W/gSumheQUi02Sr.xMjPi', true],
			['owncloud.com', '1|$2a$05$ST5E.rplNRfDCzRpzq69leRzsTGtY7k88h9Vy2eWj0Ug/iA9w5kGK', true],

			// Invalid passwords
			['password', '0|$2a$08$oKAQY5IhnZocP.61MwP7xu7TNeOb7Ostvk3j6UpacvaNMs.xRj7O2', false],
			['password', '1|$2a$08$oKAQY5IhnZocP.61MwP7xu7TNeOb7Ostvk3j6UpacvaNMs.xRj7O2', false],
			['password', '2|$2a$08$oKAQY5IhnZocP.61MwP7xu7TNeOb7Ostvk3j6UpacvaNMs.xRj7O2', false],
		];
	}


	/**
	 * @return array
	 */
	public function hashProviders72() {
		return [
			// Valid ARGON2 hashes
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$T3JGcEkxVFNOVktNSjZUcg$4/hyLtSejxNgAuzSFFV/HLM3qRQKBwEtKw61qPN4zWA', true],
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$Zk52V24yNjMzTkhyYjJKOQ$vmqHkCaOD6SiiiFKD1GeKLg/D1ynWpyZbx4XA2yed34', true],
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$R1pRcUZKamVlNndBc3l5ag$ToRhR8SiZc7fGMpOYfSc5haS5t9+Y00rljPJV7+qLkM', true],
			['nextcloud.com', '2|$argon2i$v=19$m=1024,t=2,p=2$NC9xM0FFaDlzM01QM3kudg$fSfndwtO2mKMZlKdsT8XAtPY51cSS6pLSGS3xMqeJhg', true],
			['nextcloud.com', '2|$argon2i$v=19$m=1024,t=2,p=2$UjkvUjEuL042WWl1cmdHOA$FZivLkBdZnloQsW6qq/jqWK95JSYUHW9rwQC4Ff9GN0', true],
			['nextcloud.com', '2|$argon2i$v=19$m=1024,t=2,p=2$ZnpNdUlzMEpUTW40OVpiMQ$c+yHT9dtSYsjtVGsa7UKOsxxgQAMiUc781d9WsFACqs', true],

			//Invalid ARGON2 hashes
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$UjFDUDg3cjBvM3FkbXVOWQ$7Y5xqFxSERnYn+2+7WChUpWZWMa5BEIhSHWnDgJ71Jk', false],
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$ZUxSUi5aQklXdkcyMG1uVA$sYjoSvXg/CS/aS6Xnas/o9a/OPVcGKldzzmuiCD1Fxo', false],
			['password', '2|$argon2i$v=19$m=1024,t=2,p=2$ZHQ5V0xMOFNmUC52by44Sg$DzQFk3bJTX0J4PVGwW6rMvtnBJRalBkbtpDIXR+d4A0', false],
		];
	}

	/** @var Hasher */
	protected $hasher;

	/** @var IConfig */
	protected $config;

	protected function setUp() {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);

		$this->config->method('getSystemValueInt')
			->willReturnCallback(function ($name, $default) {
				return $default;
			});

		$this->hasher = new Hasher($this->config);
	}

	public function testHash() {
		$hash = $this->hasher->hash('String To Hash');
		$this->assertNotNull($hash);
	}

	/**
	 * @dataProvider versionHashProvider
	 */
	public function testSplitHash($hash, $expected) {
		$relativePath = self::invokePrivate($this->hasher, 'splitHash', [$hash]);
		$this->assertSame($expected, $relativePath);
	}


	/**
	 * @dataProvider hashProviders70_71
	 */
	public function testVerify($password, $hash, $expected) {
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->with('passwordsalt', null)
			->will($this->returnValue('6Wow67q1wZQZpUUeI6G2LsWUu4XKx'));

		$result = $this->hasher->verify($password, $hash);
		$this->assertSame($expected, $result);
	}

	/**
	 * @dataProvider hashProviders72
	 */
	public function testVerifyArgon2i($password, $hash, $expected) {
		if (!\defined('PASSWORD_ARGON2I')) {
			$this->markTestSkipped('Need ARGON2 support to test ARGON2 hashes');
		}

		$result = $this->hasher->verify($password, $hash);
		$this->assertSame($expected, $result);
	}

	public function testUpgradeHashBlowFishToArgon2i() {
		if (!\defined('PASSWORD_ARGON2I')) {
			$this->markTestSkipped('Need ARGON2 support to test ARGON2 hashes');
		}

		$message = 'mysecret';

		$blowfish = 1 . '|' . password_hash($message, PASSWORD_BCRYPT, []);
		$argon2i  = 2 . '|' . password_hash($message, PASSWORD_ARGON2I, []);

		$this->assertTrue($this->hasher->verify($message, $blowfish,$newHash));
		$this->assertTrue($this->hasher->verify($message, $argon2i));

		$relativePath = self::invokePrivate($this->hasher, 'splitHash', [$newHash]);

		$this->assertFalse(password_needs_rehash($relativePath['hash'], PASSWORD_ARGON2I, []));
	}
}
