<?php
/**
Copyright 2011-2020 Nick Korbel

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

class ScheduleUserRepositoryTests extends TestBase
{
	public function setUp(): void
	{
		parent::setup();
	}

	public function teardown(): void
	{
		parent::teardown();
	}

	public function testPullsAllResourcesAndGroupsForUser()
	{
		$userId = 10;

		$userResourceRoles = array
		(
			array(ColumnNames::USER_ID => $userId, ColumnNames::RESOURCE_ID => 1, ColumnNames::RESOURCE_NAME => 'r1', ColumnNames::PERMISSION_TYPE => 0),
			array(ColumnNames::USER_ID => $userId, ColumnNames::RESOURCE_ID => 2, ColumnNames::RESOURCE_NAME => 'r2', ColumnNames::PERMISSION_TYPE => 0),
			array(ColumnNames::USER_ID => $userId, ColumnNames::RESOURCE_ID => 3, ColumnNames::RESOURCE_NAME => 'r3', ColumnNames::PERMISSION_TYPE => 0),
		);

		$groupResourceRoles = array
		(
			array(ColumnNames::GROUP_ID => 200, ColumnNames::RESOURCE_ID => 2, ColumnNames::RESOURCE_NAME => 'r2', ColumnNames::PERMISSION_TYPE => 0),
			array(ColumnNames::GROUP_ID => 100, ColumnNames::RESOURCE_ID => 3, ColumnNames::RESOURCE_NAME => 'r3', ColumnNames::PERMISSION_TYPE => 0),
			array(ColumnNames::GROUP_ID => 100, ColumnNames::RESOURCE_ID => 4, ColumnNames::RESOURCE_NAME => 'r4', ColumnNames::PERMISSION_TYPE => 0),
			array(ColumnNames::GROUP_ID => 200, ColumnNames::RESOURCE_ID => 5, ColumnNames::RESOURCE_NAME => 'r5', ColumnNames::PERMISSION_TYPE => 0),
		);

		$groupAdminResources = array(
			array(ColumnNames::RESOURCE_ID => 6, ColumnNames::RESOURCE_NAME => 'r6'),
		);

		$this->db->SetRow(0, $userResourceRoles);
		$this->db->SetRow(1, $groupResourceRoles);
		$this->db->SetRow(2, $groupAdminResources);

		$repo = new ScheduleUserRepository();
		$user = $repo->GetUser($userId);

		$userPermissionsCommand = new GetUserPermissionsCommand($userId);
		$groupPermissionsCommand = new SelectUserGroupPermissions($userId);

		$this->assertEquals(3, count($this->db->_Commands));
		$this->assertTrue($this->db->ContainsCommand($userPermissionsCommand));
		$this->assertTrue($this->db->ContainsCommand($groupPermissionsCommand));

		$this->assertEquals(6, count($user->GetAllResources()), 'excludes the dupes');
	}

	public function testGetsAllUniqueResourcesForUserAndGroup()
	{
		$userId = 99;

		$rid1 = 1;
		$rid2 = 2;
		$r1 = new ScheduleResource($rid1, 'resource 1');
		$r2 = new ScheduleResource($rid2, 'resource 2');
		$resources = array($r1, $r2);

		$rid3 = 3;
		$rid4 = 4;
		$r3 = new ScheduleResource($rid3, 'resource 3');
		$r4 = new ScheduleResource($rid4, 'resource 4');
        $r5 = new ScheduleResource(5, 'resource 5');
        $r6 = new ScheduleResource(6, 'resource 6');

		$g1 = new ScheduleGroup(100, array($r1, $r3), array($r5));
		$g2 = new ScheduleGroup(200, array($r1, $r4, $r3), array($r6));
		$groupPermissions = array($g1, $g2);

        $view = array($r5);

		$user = new ScheduleUser($userId, $resources, $view, $groupPermissions, array());

		$permittedResources = $user->GetAllResources();

		$this->assertEquals(6, count($permittedResources));
		$this->assertContains($r1, $permittedResources);
		$this->assertContains($r2, $permittedResources);
		$this->assertContains($r3, $permittedResources);
		$this->assertContains($r4, $permittedResources);
		$this->assertContains($r5, $permittedResources);
		$this->assertContains($r6, $permittedResources);

		$bookable = $user->GetBookableResources();
        $this->assertEquals(4, count($bookable));
        
        $viewable = $user->GetViewOnlyResources();
        $this->assertEquals(2, count($viewable));

    }
}