<?php
/**
Copyright 2012-2020 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');

class WebAuthenticationTests extends TestBase
{
	/**
	 * @var WebLoginContext
	 */
	private $loginContext;

	/**
	 * @var FakeAuth
	 */
	private $fakeAuth;

	/**
	 * @var WebAuthentication
	 */
	private $webAuth;

	private $username = 'LoGInName';
	private $password = 'password';

	public function setUp(): void
	{
		parent::setup();

		$this->fakeAuth = new FakeAuth();
		$this->loginContext = $loginContext = new WebLoginContext(new LoginData(true));
		$this->webAuth = new WebAuthentication($this->fakeAuth, $this->fakeServer);
	}

	public function testValidatePassesThrough()
	{
		$this->fakeAuth->_ValidateResult = true;

		$isValid = $this->webAuth->Validate($this->username, $this->password);

		$this->assertEquals($this->username, $this->fakeAuth->_LastLogin);
		$this->assertEquals($this->password, $this->fakeAuth->_LastPassword);
		$this->assertTrue($isValid);
	}

	public function testSuccessfulLoginSetsSession()
	{
		$user = new FakeUserSession();
		$this->fakeAuth->_Session = $user;

		$this->webAuth->Login($this->username, $this->loginContext);

		$this->assertEquals($user, $this->fakeServer->GetUserSession());
	}

	public function testCanPersistLoginWhenValidLogin()
	{
		$id = 100;
		$now = mktime(10, 11, 12, 1, 2, 2000);
		LoginTime::$Now = $now;

		$hashedValue = sprintf("%s|%s", $id, $now);

		$session = new UserSession($id);
		$session->LoginTime = $now;
		$this->fakeAuth->_Session = $session;

		$this->webAuth->Login($this->username, $this->loginContext);

		$expectedCookie = new Cookie(CookieKeys::PERSIST_LOGIN, $hashedValue);
		$this->assertEquals($expectedCookie->Value, $this->fakeServer->GetCookie(CookieKeys::PERSIST_LOGIN));
	}

	public function testCanAutoLoginWithCookie()
	{
		$userid = 'userid';
		$lastLogin = LoginTime::Now();
		$email = 'email@address.com';
		$cookie = new LoginCookie($userid, $lastLogin);

		$rows = array(
			ColumnNames::USER_ID => $userid,
			ColumnNames::LAST_LOGIN => $lastLogin,
			ColumnNames::EMAIL => $email
		);
		$this->db->SetRows(array($rows));

		$user = new FakeUserSession();
		$this->fakeAuth->_Session = $user;

		$valid = $this->webAuth->CookieLogin($cookie->Value, $this->loginContext);

		$cookieValidateCommand = new CookieLoginCommand($userid);

		$this->assertTrue($valid, 'should be valid if cookie matches');
		$this->assertEquals($cookieValidateCommand, $this->db->_LastCommand);
		$this->assertEquals($email, $this->fakeAuth->_LastLogin);
		$this->assertEquals($this->loginContext, $this->fakeAuth->_LastLoginContext);
		$this->assertEquals($user, $this->fakeServer->GetUserSession());
	}

	public function testDoesNotAutoLoginIfLastLoginDateOnCookieDoesNotMatch()
	{
		$userid = 'userid';
		$lastLogin = LoginTime::Now();
		$email = 'email@address.com';
		$cookie = new LoginCookie($userid, $lastLogin);

		$rows = array(array(
			ColumnNames::USER_ID => $userid,
			ColumnNames::LAST_LOGIN => 'not the same thing',
			ColumnNames::EMAIL => $email
		));
		$this->db->SetRows($rows);

		$valid = $this->webAuth->CookieLogin($cookie->Value, $this->loginContext);

		$this->assertFalse($valid, 'should not be valid if cookie does not match');
		$this->assertEquals(1, count($this->db->_Commands));
		$this->assertFalse($this->fakeAuth->_LoginCalled);
		$this->assertEquals(new NullUserSession(), $this->fakeServer->GetUserSession());
	}

	public function testLogsUserOut()
	{
		$userId = 100;
		$userSession = new FakeUserSession();
		$userSession->UserId = $userId;

		$loginCookie = new LoginCookie($userId, null);

		$this->webAuth->Logout($userSession);

		$this->assertEquals($loginCookie, $this->fakeServer->_DeletedCookie);
		$this->assertEquals(SessionKeys::USER_SESSION, $this->fakeServer->_EndedSession);
		$this->assertTrue($this->fakeAuth->_LogoutCalled);
	}

	public function testHandlesLoginFailure()
	{
		$page = $this->createMock('ILoginPage');
		$this->webAuth->HandleLoginFailure($page);

		$this->assertTrue($this->fakeAuth->_HandleLoginFailureCalled);
	}
}

?>