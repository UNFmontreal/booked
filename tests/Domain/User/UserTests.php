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

require_once(ROOT_DIR . 'Domain/namespace.php');

class UserTests extends TestBase
{
	public function testUserIsGroupAdminIfAtLeastOneGroupIsAnAdminGroup()
	{
		$user = new User();

		$nonAdminGroup = new UserGroup(1, 'non admin', 2, RoleLevel::NONE);
		$adminGroup = new UserGroup(2, 'admin', null, RoleLevel::GROUP_ADMIN);
		$groups = array($nonAdminGroup, $adminGroup);

		$user->WithGroups($groups);

		$this->assertTrue($user->IsGroupAdmin());
	}

	public function testUserIsApplicationAdminIfAtLeastOneGroupIsAnAdminGroup()
	{
		$user = new User();

		$nonAdminGroup = new UserGroup(1, 'non admin', 2, RoleLevel::NONE);
		$adminGroup = new UserGroup(2, 'admin', null, RoleLevel::APPLICATION_ADMIN);
		$groups = array($nonAdminGroup, $adminGroup);

		$user->WithGroups($groups);

		$this->assertTrue($user->IsInRole(RoleLevel::APPLICATION_ADMIN));
	}

	public function testUserIsResourceAdminIfAtLeastOneGroupIsAnAdminGroup()
	{
		$user = new User();

		$nonAdminGroup = new UserGroup(1, 'non admin', 2, RoleLevel::NONE);
		$adminGroup = new UserGroup(2, 'admin', null, RoleLevel::RESOURCE_ADMIN);
		$groups = array($nonAdminGroup, $adminGroup);

		$user->WithGroups($groups);

		$this->assertTrue($user->IsInRole(RoleLevel::RESOURCE_ADMIN));
	}

	public function testUserIsScheduleAdminIfAtLeastOneGroupIsAnAdminGroup()
	{
		$user = new User();

		$nonAdminGroup = new UserGroup(1, 'non admin', 2, RoleLevel::NONE);
		$adminGroup = new UserGroup(2, 'admin', null, RoleLevel::SCHEDULE_ADMIN);
		$groups = array($nonAdminGroup, $adminGroup);

		$user->WithGroups($groups);

		$this->assertTrue($user->IsInRole(RoleLevel::SCHEDULE_ADMIN));
	}

	public function testWhenUserIsInAGroupThatCanAdminAnotherGroup()
	{
		$adminGroupId = 99;
		$groupId1 = 1;
		$groupId2 = 2;

		$adminUser = new User();
		$user = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::NONE);
		$adminGroup->AddRole(RoleLevel::GROUP_ADMIN);
		$group1 = new UserGroup($groupId1, 'random group');
		$group2 = new UserGroup($groupId2, 'group with admin', $adminGroupId, RoleLevel::NONE);

		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group2);

		$adminUser->WithGroups($adminUserGroups);
		$user->WithGroups($userGroups);

		$this->assertTrue($adminUser->IsAdminFor($user), 'admin of group 2');
	}

	public function testWhenUserIsNotInAGroupThatCanAdminAnotherGroup()
	{
		$adminGroupId = 99;
		$groupId1 = 1;
		$groupId2 = 2;

		$adminUser = new User();
		$user = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::GROUP_ADMIN);
		$group1 = new UserGroup($groupId1, 'random group');
		$group2 = new UserGroup($groupId2, 'group with admin', $groupId1, RoleLevel::NONE);

		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group1, $group2);

		$adminUser->WithGroups($adminUserGroups);
		$user->WithGroups($userGroups);

		$this->assertFalse($adminUser->IsAdminFor($user), 'admin is not in any group that can admin group 1 or 2');
	}

    public function testWhenUserIsInAdminGroupForResource()
    {
        $adminGroupId = 223;
        $resource = new FakeBookableResource(1, 'n');
		$resource->SetAdminGroupId($adminGroupId);

        $adminUser = new User();
        $regularUser = new User();

        $adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::RESOURCE_ADMIN);
        $group1 = new UserGroup(1, 'random group');
        $group2 = new UserGroup(2, 'group with admin');

        $adminUserGroups = array($group1, $adminGroup);
        $userGroups = array($group1, $group2);

        $adminUser->WithGroups($adminUserGroups);
        $regularUser->WithGroups($userGroups);

        $this->assertTrue($adminUser->IsResourceAdminFor($resource));
        $this->assertFalse($regularUser->IsResourceAdminFor($resource));
    }

	public function testWhenUserIsInAdminGroupForResourcesSchedule()
	{
		$adminGroupId = 223;
		$resource = new FakeBookableResource(1, 'n');
		$resource->SetScheduleAdminGroupId($adminGroupId);

		$adminUser = new User();
		$regularUser = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::SCHEDULE_ADMIN);
		$group1 = new UserGroup(1, 'random group');
		$group2 = new UserGroup(2, 'group with admin');

		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group1, $group2);

		$adminUser->WithGroups($adminUserGroups);
		$regularUser->WithGroups($userGroups);

		$this->assertTrue($adminUser->IsResourceAdminFor($resource));
		$this->assertFalse($regularUser->IsResourceAdminFor($resource));
	}

	public function testWhenUserIsInAdminGroupForSchedule()
	{
		$scheduleId = 123;
		$adminGroupId = 223;
		$schedule = new FakeSchedule($scheduleId);
		$schedule->SetAdminGroupId($adminGroupId);

		$adminUser = new User();
		$regularUser = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::SCHEDULE_ADMIN);
		$group1 = new UserGroup(1, 'random group');
		$group2 = new UserGroup(2, 'group with admin');

		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group1, $group2);

		$adminUser->WithGroups($adminUserGroups);
		$regularUser->WithGroups($userGroups);

		$this->assertTrue($adminUser->IsScheduleAdminFor($schedule));
		$this->assertFalse($regularUser->IsScheduleAdminFor($schedule));
	}

    public function testCanGetGroupsThatUserHasAdminOver()
    {
        $user = new User();

        $adminGroup1 = new UserGroup(3, 'admin group', null, RoleLevel::GROUP_ADMIN);
        $adminGroup2 = new UserGroup(4, 'group i can admin', 3, RoleLevel::NONE);

        $groups = array($adminGroup1, $adminGroup2);

        $user->WithOwnedGroups($groups);

        $adminGroups = $user->GetAdminGroups();

        $this->assertEquals(2, count($adminGroups));
        $this->assertContains($adminGroup2, $adminGroups);
    }

	public function testIsGroupAdminForGroup()
	{
		$user = new User();
		$user->WithOwnedGroups(array(new UserGroup(1, 'g1'), new UserGroup(2, 'g2')));
		$user->WithGroups(array(new UserGroup(4, 'g4'), new UserGroup(5, 'g5')));

		$this->assertTrue($user->IsGroupAdminFor(1));
		$this->assertTrue($user->IsGroupAdminFor(2));
		$this->assertFalse($user->IsGroupAdminFor(4));
		$this->assertFalse($user->IsGroupAdminFor(5));
	}

	public function testIsGroupAdminForArray()
    {
        $user = new User();
        $user->WithOwnedGroups(array(new UserGroup(1, 'g1'), new UserGroup(2, 'g2')));
        $user->WithGroups(array(new UserGroup(4, 'g4'), new UserGroup(5, 'g5')));

        $this->assertTrue($user->IsGroupAdminFor(array(1, 100)));
        $this->assertTrue($user->IsGroupAdminFor(array(2, 100)));
        $this->assertFalse($user->IsGroupAdminFor(array(4, 100)));
        $this->assertFalse($user->IsGroupAdminFor(array(5, 100)));
    }
}