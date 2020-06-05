<?php
/**
 * Copyright 2011-2020 Nick Korbel
 *
 * This file is part of Booked Scheduler.
 *
 * Booked Scheduler is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Booked Scheduler is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'Presenters/Admin/ManageUsersPresenter.php');
require_once(ROOT_DIR . 'Pages/Admin/ManageUsersPage.php');

class ManageUsersPresenterTests extends TestBase
{
	/**
	 * @var FakeManageUsersPage
	 */
	private $page;

	/**
	 * @var FakeUserRepository
	 */
	public $userRepo;

	/**
	 * @var FakeResourceRepository
	 */
	public $resourceRepo;

	/**
	 * @var IManageUsersService|PHPUnit_Framework_MockObject_MockObject
	 */
	public $manageUsersService;

	/**
	 * @var ManageUsersPresenter
	 */
	public $presenter;

	/**
	 * @var FakeAttributeService
	 */
	public $attributeService;

	/**
	 * @var PasswordEncryption
	 */
	public $encryption;

	/**
	 * @var IGroupRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $groupRepository;

	/**
	 * @var IGroupViewRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $groupViewRepository;

	public function setUp(): void
	{
		parent::setup();

		$this->page = new FakeManageUsersPage();
		$this->userRepo = new FakeUserRepository();
		$this->resourceRepo = new FakeResourceRepository();
		$this->encryption = $this->createMock('PasswordEncryption');
		$this->manageUsersService = $this->createMock('IManageUsersService');
		$this->attributeService = new FakeAttributeService();
		$this->groupRepository = $this->createMock('IGroupRepository');
		$this->groupViewRepository = $this->createMock('IGroupViewRepository');

		$this->presenter = new ManageUsersPresenter($this->page,
													$this->userRepo,
													$this->resourceRepo,
													$this->encryption,
													$this->manageUsersService,
													$this->attributeService,
													$this->groupRepository,
													$this->groupViewRepository);
	}

	public function teardown(): void
	{
		parent::teardown();
	}

	public function testBindsUsersAndAttributesAndGroups()
	{
		$userId = 123;
		$pageNumber = 1;
		$pageSize = 10;

		$result = new UserItemView();
		$result->Id = $userId;
		$results = array($result);
		$userList = new PageableData($results);

		$resourceList = array(new FakeBookableResource(1));

		$attributeList = array(new FakeCustomAttribute(1, '1'));

		$user = new FakeUser();

		$this->page->_PageNumber = $pageNumber;
		$this->page->_PageSize = $pageSize;
		$this->page->_FilterStatusId = AccountStatus::ALL;

		$this->userRepo->_UserList = $userList;
		$this->userRepo->_User = $user;

		$this->resourceRepo->_ResourceList = $resourceList;

		$this->attributeService->_ByCategory[CustomAttributeCategory::USER] = $attributeList;

		$groups = array(new GroupItemView(1, 'gn'));
		$groupList = new PageableData($groups);
		$this->groupViewRepository
				->expects($this->once())
				->method('GetList')
				->will($this->returnValue($groupList));

		$this->presenter->PageLoad();

		$this->assertEquals($groups, $this->page->_BoundGroups);
		$this->assertEquals($userList->Results(), $this->page->_BoundUsers);
		$this->assertEquals($userList->PageInfo(), $this->page->_BoundPageInfo);
		$this->assertEquals($resourceList, $this->page->_BoundResources);
		$this->assertEquals($attributeList, $this->page->_BoundAttributes);
	}

	public function testGetsSelectedResourcesFromPageAndAssignsPermission()
	{
		$resourcesThatShouldRemainUnchanged = array(5, 10);
		$allowedResourceIds = array(1, 2, 4, 20, 30);
		$submittedResourceIds = array(1, 4);
		$currentResourceIds = array(1, 20, 30);

		$expectedResourceIds = array(1, 4, 5, 10);

		$allResourceIds = array_unique(array_merge($resourcesThatShouldRemainUnchanged, $allowedResourceIds, $submittedResourceIds, $currentResourceIds));

		$resources = array();
		foreach ($allResourceIds as $rid)
		{
			$resources[] = new FakeBookableResource($rid);
		}

		$userId = 9928;
		$adminUserId = $this->fakeUser->UserId;

		$user = new FakeUser();
		$user->WithAllowedPermissions(array_merge($resourcesThatShouldRemainUnchanged, $currentResourceIds));

		$adminUser = new FakeUser();
		$adminUser->_ResourceAdminResourceIds = $allowedResourceIds;
		$adminUser->_IsResourceAdmin = false;

		$this->page->_UserId = $userId;
		$this->page->_AllowedResourceIds = $submittedResourceIds;

		$this->resourceRepo->_ResourceList = $resources;

		$this->userRepo->_UserById[$adminUserId] = $adminUser;
		$this->userRepo->_UserById[$userId] = $user;

		$this->presenter->ChangePermissions();

		$actual = $user->GetAllowedResourceIds();
		$this->assertEquals(sort($expectedResourceIds), sort($actual));
		$this->assertEquals($this->userRepo->_UpdatedUser, $user);
	}

	public function testResetPasswordEncryptsAndUpdates()
	{
		$password = 'password';
		$salt = 'salt';
		$encrypted = 'encrypted';
		$userId = 123;

		$this->page->_UserId = $userId;

		$this->page->_Password = $password;

		$this->encryption->expects($this->once())
						 ->method('Salt')
						 ->will($this->returnValue($salt));

		$this->encryption->expects($this->once())
						 ->method('Encrypt')
						 ->with($this->equalTo($password), $this->equalTo($salt))
						 ->will($this->returnValue($encrypted));

		$user = new User();

		$this->userRepo->_User = $user;

		$this->presenter->ResetPassword();

		$this->assertEquals($encrypted, $user->encryptedPassword);
		$this->assertEquals($salt, $user->passwordSalt);
		$this->assertEquals($this->userRepo->_UpdatedUser, $user);
	}

	public function testCanUpdateUser()
	{
		$userId = 1029380;
		$fname = 'f';
		$lname = 'l';
		$username = 'un';
		$email = 'e@mail.com';
		$timezone = 'America/Chicago';
		$phone = '123-123-1234';
		$organization = 'ou';
		$position = 'position';

		$user = new FakeUser($userId);

		$attributeId = 1;
		$attributeValue = 'value';
		$attributeFormElements = array(new AttributeFormElement($attributeId, $attributeValue));
		$this->page->_UserId = $userId;
		$this->page->_FirstName = $fname;
		$this->page->_LastName = $lname;
		$this->page->_UserName = $username;
		$this->page->_Email = $email;
		$this->page->_Timezone = $timezone;
		$this->page->_Phone = $phone;
		$this->page->_Organization = $organization;
		$this->page->_Position = $position;
		$this->page->_Attributes = $attributeFormElements;

		$extraAttributes = array(
				UserAttribute::Organization => $organization,
				UserAttribute::Phone => $phone,
				UserAttribute::Position => $position);

		$this->manageUsersService->expects($this->once())
								 ->method('UpdateUser')
								 ->with($this->equalTo($userId),
										$this->equalTo($username),
										$this->equalTo($email),
										$this->equalTo($fname),
										$this->equalTo($lname),
										$this->equalTo($timezone),
										$this->equalTo($extraAttributes),
										$this->equalTo(array(new AttributeValue($attributeId, $attributeValue))))
								 ->will($this->returnValue($user));

		$this->presenter->UpdateUser();
	}

	public function testDeletesUser()
	{
		$userId = 809;
		$this->page->_UserId = $userId;

		$this->manageUsersService->expects($this->once())
								 ->method('DeleteUser')
								 ->with($this->equalTo($userId));

		$this->presenter->DeleteUser();
	}

	public function testDeletesUsers()
	{
		$userIds = array(809, 909);
		$this->page->_DeletedUserIds = $userIds;

		$this->manageUsersService->expects($this->at(0))
								 ->method('DeleteUser')
								 ->with($this->equalTo(809));

		$this->manageUsersService->expects($this->at(1))
								 ->method('DeleteUser')
								 ->with($this->equalTo(909));

		$this->presenter->DeleteMultipleUsers();
	}

	public function testAddsUser()
	{
		$fname = 'f';
		$lname = 'l';
		$username = 'un';
		$email = 'e@mail.com';
		$timezone = 'America/Chicago';
		$lang = 'foo';
		$password = 'pw';

		$attributeId = 1;
		$attributeValue = 'value';
		$attributeFormElements = array(new AttributeFormElement($attributeId, $attributeValue));

		$userId = 1090;
		$groupId = 111;

		$user = new FakeUser($userId);

		$group = new Group($groupId, 'name');
		$group->AddUser($userId);

		$this->fakeConfig->SetKey(ConfigKeys::LANGUAGE, $lang);

		$this->page->_FirstName = $fname;
		$this->page->_LastName = $lname;
		$this->page->_UserName = $username;
		$this->page->_Email = $email;
		$this->page->_Timezone = $timezone;
		$this->page->_Password = $password;
		$this->page->_Attributes = $attributeFormElements;
		$this->page->_UserGroup = $groupId;

		$this->manageUsersService->expects($this->once())
								 ->method('AddUser')
								 ->with($this->equalTo($username),
										$this->equalTo($email),
										$this->equalTo($fname),
										$this->equalTo($lname),
										$this->equalTo($password),
										$this->equalTo($timezone),
										$this->equalTo($lang),
										$this->equalTo(Pages::DEFAULT_HOMEPAGE_ID),
										$this->equalTo(array(
															   UserAttribute::Organization => null,
															   UserAttribute::Phone => null,
															   UserAttribute::Position => null)),
										$this->equalTo(array(new AttributeValue($attributeId, $attributeValue))))
								 ->will($this->returnValue($user));

		$this->groupRepository->expects($this->once())
							  ->method('LoadById')
							  ->with($this->equalTo($groupId))
							  ->will($this->returnValue($group));

		$this->groupRepository->expects($this->once())
							  ->method('Update')
							  ->with($this->equalTo($group));

		$this->presenter->AddUser();
	}

	public function testGetsAllUsers()
	{
		$users = array(new UserDto(1, 'f', 'l', 'e'));

		$this->userRepo->_AllUsers = $users;

		$this->presenter->ProcessDataRequest('all');

		$this->assertEquals($users, $this->page->_JsonResponse);
	}

	public function testParsesImportWithHeader()
	{
		$file = new FakeUploadedFile();
		$file->Contents = "username,email,first name,last name,password,phone,organization,position,timezone,language,groups\nu1,e1,f1,l1,p1,ph1,o1,po1,t1,l1,g1";
		$csv = new UserImportCsv($file, array());

		$rows = $csv->GetRows();

		$this->assertCount(1, $rows);

		$row1 = $rows[0];
		$this->assertEquals("u1", $row1->username);
		$this->assertEquals("e1", $row1->email);
		$this->assertEquals("f1", $row1->firstName);
		$this->assertEquals("l1", $row1->lastName);
		$this->assertEquals("p1", $row1->password);
		$this->assertEquals("ph1", $row1->phone);
		$this->assertEquals("o1", $row1->organization);
		$this->assertEquals("po1", $row1->position);
		$this->assertEquals("t1", $row1->timezone);
		$this->assertEquals("l1", $row1->language);
		$this->assertEquals(array("g1"), $row1->groups);
	}

	public function testDefaultsMissingColumns()
	{
		$file = new FakeUploadedFile();
		$file->Contents = "email,username,password,first name,last name\ne1,u1,p1,f1,l1";
		$csv = new UserImportCsv($file, array());

		$rows = $csv->GetRows();
		$this->assertCount(1, $rows);

		$row1 = $rows[0];
		$this->assertEquals("u1", $row1->username);
		$this->assertEquals("e1", $row1->email);
		$this->assertEquals("f1", $row1->firstName);
		$this->assertEquals("l1", $row1->lastName);
		$this->assertEquals("p1", $row1->password);
		$this->assertEquals("", $row1->phone);
		$this->assertEquals("", $row1->organization);
		$this->assertEquals("", $row1->position);
		$this->assertEquals("", $row1->timezone);
		$this->assertEquals("", $row1->language);
		$this->assertEquals(array(), $row1->groups);
	}

	public function testDefaultsMissingValuesInRow()
	{
		$file = new FakeUploadedFile();
		$file->Contents = "email,username,password,first name,last name\ne1,u1";
		$csv = new UserImportCsv($file, array());

		$rows = $csv->GetRows();
		$this->assertCount(1, $rows);

		$row1 = $rows[0];
		$this->assertEquals("u1", $row1->username);
		$this->assertEquals("e1", $row1->email);
		$this->assertEquals("", $row1->firstName);
		$this->assertEquals("", $row1->lastName);
		$this->assertEquals("", $row1->password);
		$this->assertEquals("", $row1->phone);
		$this->assertEquals("", $row1->organization);
		$this->assertEquals("", $row1->position);
		$this->assertEquals("", $row1->timezone);
		$this->assertEquals("", $row1->language);
		$this->assertEquals(array(), $row1->groups);
	}

	public function testInvalidRowsAreSkipped()
	{
		$file = new FakeUploadedFile();
		$file->Contents = "email,username,password,first name,last name\ne\ne";
		$csv = new UserImportCsv($file, array());

		$rows = $csv->GetRows();
		$skippedRowNumbers = $csv->GetSkippedRowNumbers();

		$this->assertEquals(0, count($rows));
		$this->assertEquals(array(1, 2), $skippedRowNumbers);
	}

	public function testShowsUserUpdate()
	{
		$userId = 1;
		$this->page->_UserId = $userId;

		$user = new FakeUser();
		$this->userRepo->_User = $user;
		$attributes = [1 => new FakeCustomAttribute(1)];
		$entityAttributeList = new AttributeList();
		$entityAttributeList->AddDefinition(new FakeCustomAttribute(1));
		$this->attributeService->_EntityAttributeList = $entityAttributeList;

		$this->presenter->ShowUpdate();

		$this->assertEquals($this->page->_BoundUpdateUser, $user);
		$this->assertEquals($this->page->_BoundUpdateAttributes, $attributes);
	}
}

class FakeManageUsersPage extends FakeActionPageBase implements IManageUsersPage
{

	/**
	 * @var int
	 */
	public $_UserId;
	/**
	 * @var int
	 */
	public $_PageNumber;
	/**
	 * @var int
	 */
	public $_PageSize;
	/**
	 * @var PageInfo
	 */
	public $_BoundPageInfo;
	/**
	 * @var UserItemView[]
	 */
	public $_BoundUsers;
	/**
	 * @var BookableResource[]
	 */
	public $_BoundResources;
	/**
	 * @var GroupItemView[]
	 */
	public $_BoundGroups;
	/**
	 * @var CustomAttribute[]
	 */
	public $_BoundAttributes;
	/**
	 * @var int
	 */
	public $_FilterStatusId;
	/**
	 * @var int[]
	 */
	public $_AllowedResourceIds;
	/**
	 * @var string
	 */
	public $_Password;
	/**
	 * @var string
	 */
	public $_FirstName;
	/**
	 * @var string
	 */
	public $_LastName;
	/**
	 * @var string
	 */
	public $_UserName;
	/**
	 * @var string
	 */
	public $_Email;
	/**
	 * @var string
	 */
	public $_Timezone;
	/**
	 * @var string
	 */
	public $_Phone;
	/**
	 * @var string
	 */
	public $_Organization;
	/**
	 * @var string
	 */
	public $_Position;
	/**
	 * @var int[]
	 */
	public $_DeletedUserIds;
	public $_Language;
	/**
	 * @var AttributeFormElement[]
	 */
	public $_Attributes;
	/**
	 * @var int
	 */
	public $_UserGroup;
	public $_JsonResponse;
	/**
	 * @var User
	 */
	public $_BoundUpdateUser;
	/**
	 * @var CustomAttribute[]
	 */
	public $_BoundUpdateAttributes;

	function GetPageNumber()
	{
		return $this->_PageNumber;
	}

	function GetPageSize()
	{
		return $this->_PageSize;
	}

	function BindPageInfo(PageInfo $pageInfo)
	{
		$this->_BoundPageInfo = $pageInfo;
	}

	function BindUsers($users)
	{
		$this->_BoundUsers = $users;
	}

	public function GetUserId()
	{
		return $this->_UserId;
	}

	public function BindResources($resources)
	{
		$this->_BoundResources = $resources;
	}

	public function SetJsonResponse($objectToSerialize)
	{
		$this->_JsonResponse = $objectToSerialize;
	}

	public function GetAllowedResourceIds()
	{
		return $this->_AllowedResourceIds;
	}

	public function GetPassword()
	{
		return $this->_Password;
	}

	public function GetEmail()
	{
		return $this->_Email;
	}

	public function GetUserName()
	{
		return $this->_UserName;
	}

	public function GetFirstName()
	{
		return $this->_FirstName;
	}

	public function GetLastName()
	{
		return $this->_LastName;
	}

	public function GetTimezone()
	{
		return $this->_Timezone;
	}

	public function GetPhone()
	{
		return $this->_Phone;
	}

	public function GetPosition()
	{
		return $this->_Position;
	}

	public function GetOrganization()
	{
		return $this->_Organization;
	}

	public function GetLanguage()
	{
		return $this->_Language;
	}

	public function BindAttributeList($attributeList)
	{
		$this->_BoundAttributes = $attributeList;
	}

	public function GetAttributes()
	{
		return $this->_Attributes;
	}

	public function GetFilterStatusId()
	{
		return $this->_FilterStatusId;
	}

	public function GetUserGroup()
	{
		return $this->_UserGroup;
	}

	public function BindGroups($groups)
	{
		$this->_BoundGroups = $groups;
	}

	public function GetReservationColor()
	{
		// TODO: Implement GetReservationColor() method.
	}

	public function GetValue()
	{
		// TODO: Implement GetValue() method.
	}

	public function GetName()
	{
		// TODO: Implement GetName() method.
	}

	public function ShowTemplateCSV($attributes)
	{
		// TODO: Implement ShowTemplateCSV() method.
	}

	public function GetImportFile()
	{
		// TODO: Implement GetImportFile() method.
	}

	public function SetImportResult($importResult)
	{
		// TODO: Implement SetImportResult() method.
	}

	public function GetInvitedEmails()
	{
		// TODO: Implement GetInvitedEmails() method.
	}

	public function ShowExportCsv()
	{
		// TODO: Implement ShowExportCsv() method.
	}

	public function BindStatusDescriptions()
	{
		// TODO: Implement BindStatusDescriptions() method.
	}

	public function GetDeletedUserIds()
	{
		return $this->_DeletedUserIds;
	}

	public function SendEmailNotification()
	{
		// TODO: Implement SendEmailNotification() method.
	}

	public function GetUpdateOnImport()
	{
		// TODO: Implement GetUpdateOnImport() method.
	}

	public function ShowUserUpdate(User $user, $attributes)
	{
		$this->_BoundUpdateUser = $user;
		$this->_BoundUpdateAttributes = $attributes;
	}
}
