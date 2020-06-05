<?php
/**
Copyright 2013-2020 Nick Korbel

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

use PHPUnit\Framework\MockObject\MockObject;

require_once(ROOT_DIR . 'WebServices/UsersWriteWebService.php');

class UsersWriteWebServiceTests extends TestBase
{
	/**
	 * @var UsersWriteWebService
	 */
	private $service;

	/**
	 * @var FakeRestServer
	 */
	private $server;

	/**
	 * @var MockObject|IUserSaveController
	 */
	private $controller;

	public function setUp(): void
	{
		parent::setup();

		$this->server = new FakeRestServer();
		$this->controller = $this->createMock('IUserSaveController');

		$this->service = new UsersWriteWebService($this->server, $this->controller);
	}

	public function testCanCreateNewUser()
	{
		$userId = '1';

		$userRequest = new CreateUserRequest();
		$this->server->SetRequest($userRequest);

		$controllerResult = new UserControllerResult($userId);

		$this->controller->expects($this->once())
				->method('Create')
				->with($this->equalTo($userRequest), $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Create();

		$this->assertEquals(new UserCreatedResponse($this->server, $userId), $this->server->_LastResponse);
	}

	public function testFailedCreate()
	{
		$userRequest = new CreateUserRequest();
		$this->server->SetRequest($userRequest);

		$errors = array('error');
		$controllerResult = new UserControllerResult(null, $errors);

		$this->controller->expects($this->once())
				->method('Create')
				->with($this->equalTo($userRequest), $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Create();

		$this->assertEquals(new FailedResponse($this->server, $errors), $this->server->_LastResponse);
		$this->assertEquals(RestResponse::BAD_REQUEST_CODE, $this->server->_LastResponseCode);
	}

	public function testCanUpdateUser()
	{
		$userId = '1';

		$userRequest = new UpdateUserRequest();
		$this->server->SetRequest($userRequest);

		$controllerResult = new UserControllerResult($userId);

		$this->controller->expects($this->once())
				->method('Update')
				->with($this->equalTo($userId), $this->equalTo($userRequest),
					   $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Update($userId);

		$this->assertEquals(new UserUpdatedResponse($this->server, $userId), $this->server->_LastResponse);
	}

	public function testFailedUpdate()
	{
		$userId = 123;
		$userRequest = new UpdateUserRequest();
		$this->server->SetRequest($userRequest);

		$errors = array('error');
		$controllerResult = new UserControllerResult(null, $errors);

		$this->controller->expects($this->once())
				->method('Update')
				->with($this->equalTo($userId), $this->equalTo($userRequest),
					   $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Update($userId);

		$this->assertEquals(new FailedResponse($this->server, $errors), $this->server->_LastResponse);
		$this->assertEquals(RestResponse::BAD_REQUEST_CODE, $this->server->_LastResponseCode);
	}

	public function testCanDeleteUser()
	{
		$userId = '1';

		$controllerResult = new UserControllerResult($userId);

		$this->controller->expects($this->once())
				->method('Delete')
				->with($this->equalTo($userId), $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Delete($userId);

		$this->assertEquals(new DeletedResponse(), $this->server->_LastResponse);
	}

	public function testFailedDelete()
	{
		$userId = 123;

		$errors = array('error');
		$controllerResult = new UserControllerResult(null, $errors);

		$this->controller->expects($this->once())
				->method('Delete')
				->with($this->equalTo($userId), $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->Delete($userId);

		$this->assertEquals(new FailedResponse($this->server, $errors), $this->server->_LastResponse);
		$this->assertEquals(RestResponse::BAD_REQUEST_CODE, $this->server->_LastResponseCode);
	}

	public function testCanUpdatePassword()
	{
		$userId = '1';
        $password = 'new password';

        $this->server->_Request = new UpdateUserPasswordRequest();
        $this->server->_Request->password = $password;

		$controllerResult = new UserControllerResult($userId);

		$this->controller->expects($this->once())
				->method('UpdatePassword')
				->with($this->equalTo($userId), $this->equalTo($password), $this->equalTo($this->server->GetSession()))
				->will($this->returnValue($controllerResult));

		$this->service->UpdatePassword($userId);

		$this->assertEquals(new UserUpdatedResponse($this->server, $userId), $this->server->_LastResponse);
	}

	public function testFailedPasswordUpdate()
	{
		$userId = 123;
        $password = 'new password';

		$errors = array('error');
		$controllerResult = new UserControllerResult(null, $errors);

		$this->server->_Request = new UpdateUserPasswordRequest();
		$this->server->_Request->password = $password;

		$this->controller->expects($this->once())
            ->method('UpdatePassword')
            ->with($this->equalTo($userId), $this->equalTo($password), $this->equalTo($this->server->GetSession()))
            ->will($this->returnValue($controllerResult));

        $this->service->UpdatePassword($userId);

		$this->assertEquals(new FailedResponse($this->server, $errors), $this->server->_LastResponse);
		$this->assertEquals(RestResponse::BAD_REQUEST_CODE, $this->server->_LastResponseCode);
	}
}