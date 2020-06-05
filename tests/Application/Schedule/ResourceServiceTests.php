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

require_once(ROOT_DIR . 'lib/Application/Schedule/ResourceService.php');

class ResourceServiceTests extends TestBase
{
	/**
	 * @var IPermissionService|PHPUnit_Framework_MockObject_MockObject
	 */
	private $permissionService;

	/**
	 * @var IResourceRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $resourceRepository;

	/**
	 * @var IAttributeService|PHPUnit_Framework_MockObject_MockObject
	 */
	private $attributeService;

	/**
	 * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $userRepository;

	/**
	 * @var ResourceService
	 */
	private $resourceService;

	/**
	 * @var IAccessoryRepository
	 */
	private $accessoryRepository;

	public function setUp(): void
	{
		$this->permissionService = $this->createMock('IPermissionService');
		$this->resourceRepository = $this->createMock('IResourceRepository');
		$this->attributeService = $this->createMock('IAttributeService');
		$this->userRepository = $this->createMock('IUserRepository');
		$this->accessoryRepository = $this->createMock('IAccessoryRepository');

		$this->resourceService = new ResourceService($this->resourceRepository, $this->permissionService, $this->attributeService, $this->userRepository, $this->accessoryRepository);

		parent::setup();
	}

	public function testResourceServiceChecksPermissionForEachResource()
	{
		$scheduleId = 100;
		$user = $this->fakeUser;

		$resource1 = new FakeBookableResource(1, 'resource1');
		$resource2 = new FakeBookableResource(2, 'resource2');
		$resource3 = new FakeBookableResource(3, 'resource3');
		$resource4 = new FakeBookableResource(4, 'resource4');
		$resources = array($resource1, $resource2, $resource3, $resource4);

		$this->resourceRepository
				->expects($this->once())
				->method('GetScheduleResources')
				->with($this->equalTo($scheduleId))
				->will($this->returnValue($resources));

		$this->permissionService
				->expects($this->at(0))
				->method('CanAccessResource')
				->with($this->equalTo($resource1),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(1))
				->method('CanBookResource')
				->with($this->equalTo($resource1),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(2))
				->method('CanAccessResource')
				->with($this->equalTo($resource2),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(3))
				->method('CanBookResource')
				->with($this->equalTo($resource2),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(4))
				->method('CanAccessResource')
				->with($this->equalTo($resource3),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(5))
				->method('CanBookResource')
				->with($this->equalTo($resource3),
					   $this->equalTo($user))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(6))
				->method('CanAccessResource')
				->with($this->equalTo($resource4),
					   $this->equalTo($user))
				->will($this->returnValue(false));

		$resourceDto1 = new ResourceDto(1, 'resource1', true, true, $resource1->GetScheduleId(), $resource1->GetMinLength(), $resource1->GetResourceTypeId(),
														$resource1->GetAdminGroupId(), $resource1->GetScheduleAdminGroupId(), $resource1->GetStatusId(), $resource1->GetRequiresApproval(),
														$resource1->IsCheckInEnabled(), $resource1->IsAutoReleased(), $resource1->GetAutoReleaseMinutes(), $resource1->GetColor());
		$resourceDto2 = new ResourceDto(2, 'resource2', true, true, $resource2->GetScheduleId(), $resource2->GetMinLength(), $resource2->GetResourceTypeId(),
														$resource2->GetAdminGroupId(), $resource2->GetScheduleAdminGroupId(), $resource2->GetStatusId(), $resource2->GetRequiresApproval(),
														$resource2->IsCheckInEnabled(), $resource2->IsAutoReleased(), $resource2->GetAutoReleaseMinutes(), $resource2->GetColor());
		$resourceDto3 = new ResourceDto(3, 'resource3', true, true, $resource3->GetScheduleId(), $resource3->GetMinLength(), $resource3->GetResourceTypeId(),
														$resource3->GetAdminGroupId(), $resource3->GetScheduleAdminGroupId(), $resource3->GetStatusId(), $resource3->GetRequiresApproval(),
														$resource3->IsCheckInEnabled(), $resource3->IsAutoReleased(), $resource3->GetAutoReleaseMinutes(), $resource3->GetColor());
		$resourceDto4 = new ResourceDto(4, 'resource4', false, false, $resource4->GetScheduleId(), $resource4->GetMinLength(), $resource4->GetResourceTypeId(),
														$resource4->GetAdminGroupId(), $resource4->GetScheduleAdminGroupId(), $resource4->GetStatusId(), $resource4->GetRequiresApproval(),
														$resource4->IsCheckInEnabled(), $resource4->IsAutoReleased(), $resource4->GetAutoReleaseMinutes(), $resource4->GetColor());
		$expected = array($resourceDto1, $resourceDto2, $resourceDto3, $resourceDto4);

		$actual = $this->resourceService->GetScheduleResources($scheduleId, true, $user);

		$this->assertEquals($expected, $actual);
	}

	public function testGetAllChecksPermissionForEachResource()
	{
		$session = $this->fakeUser;

		$resource1 = new FakeBookableResource(1, 'resource1');
		$resource2 = new FakeBookableResource(2, 'resource2');
		$resources = array($resource1, $resource2);

		$user = new FakeUser();
		$user->_IsResourceAdmin = true;

		$this->resourceRepository
				->expects($this->once())
				->method('GetResourceList')
				->will($this->returnValue($resources));

		$this->permissionService
				->expects($this->at(0))
				->method('CanAccessResource')
				->with($this->equalTo($resource1),
					   $this->equalTo($session))
				->will($this->returnValue(false));

		$this->permissionService
				->expects($this->at(1))
				->method('CanAccessResource')
				->with($this->equalTo($resource2),
					   $this->equalTo($session))
				->will($this->returnValue(true));

		$this->permissionService
				->expects($this->at(2))
				->method('CanBookResource')
				->with($this->equalTo($resource2),
					   $this->equalTo($session))
				->will($this->returnValue(true));

		$this->userRepository->expects($this->any())
							->method('LoadById')
							->with($this->equalTo($session->UserId))
							->will($this->returnValue($user));

		$resourceDto1 = new ResourceDto(1, 'resource1', false, false, $resource1->GetScheduleId(), $resource1->GetMinLength(), $resource1->GetResourceTypeId(),
														$resource1->GetAdminGroupId(), $resource1->GetScheduleAdminGroupId(), $resource1->GetStatusId(), $resource1->GetRequiresApproval(),
														$resource1->IsCheckInEnabled(), $resource1->IsAutoReleased(), $resource1->GetAutoReleaseMinutes(), $resource1->GetColor());
		$resourceDto2 = new ResourceDto(2, 'resource2', true, true, $resource2->GetScheduleId(), $resource2->GetMinLength(), $resource2->GetResourceTypeId(),
														$resource2->GetAdminGroupId(), $resource2->GetScheduleAdminGroupId(), $resource2->GetStatusId(), $resource2->GetRequiresApproval(),
														$resource2->IsCheckInEnabled(), $resource2->IsAutoReleased(), $resource2->GetAutoReleaseMinutes(), $resource1->GetColor());

		$expected = array($resourceDto1, $resourceDto2);

		$actual = $this->resourceService->GetAllResources(true, $session);

		$this->assertEquals($expected, $actual);
	}

	public function testChecksStatusOfEachResourceWhenGettingAll()
	{
		$scheduleId = 100;
		$session = $this->fakeUser;

		$user = new FakeUser();
		$user->_IsResourceAdmin = false;

		$resource1 = new FakeBookableResource(1, 'resource1');
		$resource1->ChangeStatus(ResourceStatus::UNAVAILABLE);
		$resources = array($resource1);

		$this->resourceRepository
				->expects($this->any())
				->method('GetScheduleResources')
				->with($this->equalTo($scheduleId))
				->will($this->returnValue($resources));

		$this->permissionService
				->expects($this->at(0))
				->method('CanAccessResource')
				->with($this->equalTo($resource1),
					   $this->equalTo($session))
				->will($this->returnValue(true));

		$this->userRepository->expects($this->any())
					->method('LoadById')
					->with($this->equalTo($session->UserId))
					->will($this->returnValue($user));

		$resourceDto1 = new ResourceDto(1, 'resource1', false, false, $resource1->GetScheduleId(), $resource1->GetMinLength(), $resource1->GetResourceTypeId(),
										$resource1->GetAdminGroupId(), $resource1->GetScheduleAdminGroupId(), $resource1->GetStatusId(), $resource1->GetRequiresApproval(),
										$resource1->IsCheckInEnabled(), $resource1->IsAutoReleased(), $resource1->GetAutoReleaseMinutes(), $resource1->GetColor());

		$expected = array($resourceDto1);

		$actualInclusive = $this->resourceService->GetScheduleResources($scheduleId, true, $session);

		$this->assertEquals($expected, $actualInclusive);

		$actualExcluded = $this->resourceService->GetScheduleResources($scheduleId, false, $session);
		$this->assertEquals(array(), $actualExcluded);
	}

	public function testResourcesAreNotReturnedIfNotIncludingInaccessibleResources()
	{
		$scheduleId = 100;
		$user = $this->fakeUser;

		$resource1 = new FakeBookableResource(1, 'resource1');

		$this->resourceRepository
				->expects($this->once())
				->method('GetScheduleResources')
				->with($this->equalTo($scheduleId))
				->will($this->returnValue(array($resource1)));

		$this->permissionService
				->expects($this->at(0))
				->method('CanAccessResource')
				->with($this->equalTo($resource1))
				->will($this->returnValue(false));

		$includeInaccessibleResources = false;
		$actual = $this->resourceService->GetScheduleResources($scheduleId, $includeInaccessibleResources, $user);

		$this->assertEquals(0, count($actual));
	}

	public function testGetsAccessoriesFromRepository()
	{
		$accessories = array(new Accessory(4, "lksjdf", 23));

		$this->accessoryRepository
				->expects($this->once())
				->method('LoadAll')
				->will($this->returnValue($accessories));

		$actualAccessories = $this->resourceService->GetAccessories();

		$this->assertEquals($accessories, $actualAccessories);
	}

	public function testFiltersResources()
	{
		$scheduleId = 122;
		$resourceId = 4;

		$resource1 = new FakeBookableResource(1, 'resource1');
		$resource2 = new FakeBookableResource(2, 'resource2');
		$resource3 = new FakeBookableResource(3, 'resource3');
		$resource4 = new FakeBookableResource(4, 'resource4');
		$resources = array($resource1, $resource2, $resource3, $resource4);

		$this->resourceRepository
				->expects($this->once())
				->method('GetScheduleResources')
				->with($this->equalTo($scheduleId))
				->will($this->returnValue($resources));

		$this->permissionService
				->expects($this->any())
				->method('CanAccessResource')
				->with($this->anything(), $this->anything())
				->will($this->returnValue(true));

		$filter = $this->createMock('IScheduleResourceFilter');

		$filter->expects($this->once())
			   ->method('FilterResources')
			   ->with($this->equalTo($resources), $this->equalTo($this->resourceRepository))
			   ->will($this->returnValue(array($resourceId)));

		$resources = $this->resourceService->GetScheduleResources($scheduleId, true, $this->fakeUser, $filter);

		$this->assertEquals(1, count($resources));
		$this->assertEquals(4, $resources[0]->GetId());
	}

	public function testGetsResourceCustomAttributes()
	{
		$customAttributes = array(new FakeCustomAttribute(1));

		$this->attributeService->expects($this->once())
							   ->method('GetByCategory')
							   ->with($this->equalTo(CustomAttributeCategory::RESOURCE))
							   ->will($this->returnValue($customAttributes));

		$attributes = $this->resourceService->GetResourceAttributes();

		$this->assertEquals(1, count($attributes));
		$this->assertEquals($customAttributes[0]->Id(), $attributes[0]->Id());
	}

	public function testGetsResourceTypeCustomAttributes()
	{
		$customAttributes = array(new FakeCustomAttribute(1));

		$this->attributeService->expects($this->once())
							   ->method('GetByCategory')
							   ->with($this->equalTo(CustomAttributeCategory::RESOURCE_TYPE))
							   ->will($this->returnValue($customAttributes));

		$attributes = $this->resourceService->GetResourceTypeAttributes();

		$this->assertEquals(1, count($attributes));
		$this->assertEquals($customAttributes[0]->Id(), $attributes[0]->Id());
	}

	public function testFiltersResourcesWhenGettingResourceGroups()
	{
		$scheduleId = 18;
		$expectedGroups = new FakeResourceGroupTree();
		$expectedGroups->AddGroup(new ResourceGroup(1, 'g'));

		$this->resourceRepository->expects($this->once())
								 ->method('GetResourceGroups')
								 ->with($this->equalTo($scheduleId), $this->anything())
								 ->will($this->returnValue($expectedGroups));

		$groups = $this->resourceService->GetResourceGroups($scheduleId, $this->fakeUser);

		$this->assertEquals($expectedGroups, $groups);
	}
}