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
use Friendica\Test\DiceHttpMockHandlerTrait;
use Friendica\Model\User as UserModel;
use Friendica\DI;
use Dice\Dice;
use Friendica\Core\Logger;

class FediTest extends MockedTest
{
	use DiceHttpMockHandlerTrait;

	protected function setUp(): void
	{
		parent::setUp();
	}

	protected function tearDown(): void
	{
		$this->tearDownFixtures();

		parent::tearDown();
	}

    /**
     * @runInSeparateProcess
     */
    public function testNothing()
    {
        $oldserver = $_SERVER;
        $_SERVER["REDIRECT_REMOTE_USER"] = "";
        $_SERVER["REDIRECT_HTTPS"] = "on";
        $_SERVER["REDIRECT_SSL_TLS_SNI"] = "friendica.local";
        $_SERVER["REDIRECT_STATUS"] = "200";
        $_SERVER["HTTPS"] = "on";
        $_SERVER["SSL_TLS_SNI"] = "friendica.local";
        $_SERVER["HTTP_HOST"] = "friendica.local";
        $_SERVER["HTTP_USER_AGENT"] = "curl/7.81.0";
        $_SERVER["HTTP_ACCEPT"] = "application/activity+json";
        $_SERVER["PATH"] = "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin";
        $_SERVER["SERVER_SIGNATURE"] = "<address>Apache/2.4.52 (Ubuntu) Server at friendica.local Port 443</address>\n";
        $_SERVER["SERVER_SOFTWARE"] = "Apache/2.4.52 (Ubuntu)";
        $_SERVER["SERVER_NAME"] = "friendica.local";
        $_SERVER["SERVER_ADDR"] = "192.168.56.30";
        $_SERVER["SERVER_PORT"] = "443";
        $_SERVER["REMOTE_ADDR"] = "192.168.56.1";
        $_SERVER["DOCUMENT_ROOT"] = "/var/www/friendica";
        $_SERVER["REQUEST_SCHEME"] = "https";
        $_SERVER["CONTEXT_PREFIX"] = "";
        $_SERVER["CONTEXT_DOCUMENT_ROOT"] = "/var/www/friendica";
        $_SERVER["SERVER_ADMIN"] = "[no address given]";
        $_SERVER["SCRIPT_FILENAME"] = "/var/www/friendica/index.php";
        $_SERVER["REMOTE_PORT"] = "60430";
        $_SERVER["REDIRECT_URL"] = "/.well-known/webfinger";
        $_SERVER["REDIRECT_QUERY_STRING"] = "pagename=%2ewell%2dknown%2fwebfinger&resource=acct:test_user@friendica.local";
        $_SERVER["GATEWAY_INTERFACE"] = "CGI/1.1";
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["QUERY_STRING"] = "pagename=%2ewell%2dknown%2fwebfinger&resource=acct:test_user@friendica.local";
        $_SERVER["REQUEST_URI"] = "/.well-known/webfinger?resource=acct:test_user@friendica.local";
        $_SERVER["SCRIPT_NAME"] = "/index.php";
        $_SERVER["PHP_SELF"] = "/index.php";
        $_SERVER["REQUEST_TIME_FLOAT"] = 1717159701.122909;
        $_SERVER["REQUEST_TIME"] = 1717159701;
        $_GET['resource'] = "acct:test_user@friendica.local";

		$this->setupHttpMockHandler();
        $this->start_time = microtime(true);
        $this->dice = (new Dice())->addRules(include __DIR__ . '/../../static/dependencies.config.php');
        \Friendica\DI::init($this->dice);
        $this->a = \Friendica\DI::app();
        \Friendica\DI::mode()->setExecutor(\Friendica\App\Mode::INDEX);
        DI::config()->set('system', 'disable_email_validation', true);
        if (!UserModel::getByNickname("test_user")) {
            $this->test_user = UserModel::createMinimal("Test User", "test_user@social.test", "test_user");
        }

        Friendica\Core\System::setBypassExit();
        self::assertTrue(true);
        ob_start();
        $this->a->runFrontend(
            $this->dice->create(\Friendica\App\Router::class),
            $this->dice->create(\Friendica\Core\PConfig\Capability\IManagePersonalConfigValues::class),
            $this->dice->create(\Friendica\Security\Authentication::class),
            $this->dice->create(\Friendica\App\Page::class),
            $this->dice->create(\Friendica\Content\Nav::class),
            $this->dice->create(\Friendica\Module\Special\HTTPException::class),
            new \Friendica\Util\HTTPInputData($_SERVER),
            $this->start_time,
            $_SERVER
        );
        $result = ob_get_clean();
        echo $result;
    }
}
