<?php
/**
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Test;

use OC\AvatarManager;
use OC\User\User;
use OCP\Files\IRootFolder;
use OCP\Files\Storage\IStorage;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUserManager;
use Test\Traits\MountProviderTrait;

/**
 * Class AvatarManagerTest
 */
class AvatarManagerTest extends TestCase {
	use MountProviderTrait;

	/** @var AvatarManager | \PHPUnit_Framework_MockObject_MockObject */
	private $avatarManager;

	/** @var \OC\Files\Storage\Temporary */
	private $storage;

	/** @var IUserManager | \PHPUnit_Framework_MockObject_MockObject */
	private $userManager;

	/** @var IRootFolder | \PHPUnit_Framework_MockObject_MockObject */
	private $rootFolder;

	public function setUp() {
		parent::setUp();

		$this->userManager = $this->createMock(IUserManager::class);
		$this->rootFolder = $this->createMock(IRootFolder::class);
		$l = $this->createMock(IL10N::class);
		$logger = $this->createMock(ILogger::class);

		$this->storage = new \OC\Files\Storage\Temporary();
		$this->registerMount('valid-user', $this->storage, '/valid-user/');

		$this->avatarManager = $this->getMockBuilder(AvatarManager::class)
			->setMethods(['getAvatarStorage'])
			->setConstructorArgs([$this->userManager,
				$this->rootFolder,
				$l,
				$logger])
			->getMock();
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage user does not exist
	 */
	public function testGetAvatarInvalidUser() {
		$this->avatarManager->getAvatar('invalidUser');
	}

	public function testGetAvatarValidUser() {
		$user = $this->createMock(User::class);
		$this->userManager->expects($this->once())->method('get')->willReturn($user);

		$storage = $this->createMock(IStorage::class);
		$this->avatarManager->expects($this->once())->method('getAvatarStorage')->willReturn($storage);

		$avatar = $this->avatarManager->getAvatar('valid-user');

		$this->assertInstanceOf('\OCP\IAvatar', $avatar);
		$this->assertFalse($this->storage->file_exists('files'));
	}

	public function testGetAvatarValidUserDifferentCasing() {
		$user = $this->createMock(User::class);
		$this->userManager->expects($this->once())
			->method('get')
			->with('vaLid-USER')
			->willReturn($user);

		$storage = $this->createMock(IStorage::class);
		$this->avatarManager->expects($this->once())
			->method('getAvatarStorage')
			->willReturn($storage);

		$avatar = $this->avatarManager->getAvatar('vaLid-USER');
		$this->assertInstanceOf('\OCP\IAvatar', $avatar);
		$this->assertFalse($this->storage->file_exists('files'));
	}
}
