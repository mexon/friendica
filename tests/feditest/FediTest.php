<?php
/**
 * @copyright Copyright (C) 2010-2024, the Friendica project
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

use Friendica\Test\MockedTest;
use Friendica\Model\User as UserModel;
use Friendica\DI;
use Dice\Dice;
use Friendica\Core\Logger;

class FediTest extends MockedTest
{
	protected function setUp(): void
	{
		parent::setUp();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    /**
     * @runInSeparateProcess
     */
    public function testNothing()
    {
        $_SERVER["SERVER_NAME"] = "friendica.local";
        $_SERVER["QUERY_STRING"] = "pagename=%2ewell%2dknown%2fwebfinger&resource=acct:test_user@friendica.local";
        $_GET['resource'] = "acct:test_user@friendica.local";

        $this->dice = (new Dice())->addRules(include __DIR__ . '/../../static/dependencies.config.php');
        \Friendica\DI::init($this->dice);
        $this->a = \Friendica\DI::app();
        DI::config()->set('system', 'disable_email_validation', true);
        if (!UserModel::getByNickname("test_user")) {
            $this->test_user = UserModel::createMinimal("Test User", "test_user@social.test", "test_user");
        }

        Friendica\Core\System::setBypassExit();
        self::assertTrue(true);
        ob_start();
        try {
        $this->a->runFrontend(
            $this->dice->create(\Friendica\App\Router::class),
            $this->dice->create(\Friendica\Core\PConfig\Capability\IManagePersonalConfigValues::class),
            $this->dice->create(\Friendica\Security\Authentication::class),
            $this->dice->create(\Friendica\App\Page::class),
            $this->dice->create(\Friendica\Content\Nav::class),
            $this->dice->create(\Friendica\Module\Special\HTTPException::class),
            new \Friendica\Util\HTTPInputData($_SERVER),
            microtime(true),
            $_SERVER
        );
        } catch (Friendica\Core\ExitException $e) {
        }
        $result = json_decode(ob_get_clean());
        self::assertIsObject($result);
        self::assertIsArray($result->links);
        foreach ($result->links as $link) {
            if (property_exists($link, "rel") and $link->rel == "self" and property_exists($link, "type") and $link->type == "application/activity+json") {
                $self_link = $link;
                break;
            }
        }
        self::assertEquals($link->href, "https://friendica.local/profile/test_user");
    }
}
