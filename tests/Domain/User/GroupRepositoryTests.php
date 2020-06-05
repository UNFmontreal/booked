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

require_once(ROOT_DIR . 'Domain/Access/namespace.php');

class GroupRepositoryTests extends TestBase
{
    /**
     * @var GroupRepository
     */
    private $repository;

    public function setUp(): void
    {
        parent::setup();

        $this->repository = new GroupRepository();
    }

    public function teardown(): void
    {
        parent::teardown();
    }

    public function testCanGetPageableListOfGroups()
    {
        $filter = new SqlFilterEquals("cn", "cv");
        $pageNum = 10;
        $pageSize = 100;
        $count = 1000;

        $countRow = array(ColumnNames::TOTAL => $count);
        $itemRows = new GroupItemRow();
        $itemRows->With(1, 'g1')->With(2, 'g2');
        $rows = $itemRows->Rows();

        $this->db->SetRow(0, array($countRow));
        $this->db->SetRow(1, $rows);

        $baseCommand = new GetAllGroupsCommand();
        $expected = new FilterCommand($baseCommand, $filter);

        $list = $this->repository->GetList($pageNum, $pageSize, null, null, $filter);

        $results = $list->Results();
        $this->assertEquals(GroupItemView::Create($rows[0]), $results[0]);
        $this->assertEquals(GroupItemView::Create($rows[1]), $results[1]);
        $this->assertTrue($this->db->ContainsCommand($expected), "missing select group command");

        $pageInfo = $list->PageInfo();
        $this->assertEquals($count, $pageInfo->Total);
        $this->assertEquals($pageNum, $pageInfo->CurrentPage);
    }

    public function testCanGetGroupUsers()
    {
        $rows[] = $this->GetGroupUserRow(1, 'f', 'l');
        $rows[] = $this->GetGroupUserRow(2, '2f', '2l');
        $this->db->SetRow(0, array(array(ColumnNames::TOTAL => 20)));
        $this->db->SetRow(1, $rows);

        $groupId = 50;
        $users = $this->repository->GetUsersInGroup($groupId, 1, 20);

        $actualCommand = $this->db->_LastCommand;

        $this->assertEquals(new GetAllGroupUsersCommand($groupId, AccountStatus::ACTIVE), $actualCommand);

        $results = $users->Results();
        $this->assertEquals(2, count($results));
        $this->assertEquals(1, $results[0]->Id);
    }

    public function testCanLoadById()
    {
        $groupId = 98282;
        $groupName = 'gn';
        $groupAdminId = 1111;
        $isDefault = 1;

        $rows = array();
        $rows[] = array(ColumnNames::GROUP_ID => $groupId,
            ColumnNames::GROUP_NAME => $groupName,
            ColumnNames::GROUP_ADMIN_GROUP_ID => $groupAdminId,
            ColumnNames::GROUP_ISDEFAULT => $isDefault);

        $groupUsers = array(
            array(ColumnNames::USER_ID => 1),
            array(ColumnNames::USER_ID => 2),
        );
        $permissions = array(
            array(ColumnNames::GROUP_ID => 1, ColumnNames::RESOURCE_ID => 1),
            array(ColumnNames::GROUP_ID => 1, ColumnNames::RESOURCE_ID => 2),

        );
        $roles = array(
            array(ColumnNames::ROLE_ID => 1, ColumnNames::ROLE_NAME => 'thing', ColumnNames::ROLE_LEVEL => RoleLevel::NONE),
            array(ColumnNames::ROLE_ID => 2, ColumnNames::ROLE_NAME => 'name', ColumnNames::ROLE_LEVEL => RoleLevel::GROUP_ADMIN),

        );
        $this->db->SetRow(0, $rows);
        $this->db->SetRow(1, $groupUsers);
        $this->db->SetRow(2, $permissions);
        $this->db->SetRow(3, $roles);

        $group = $this->repository->LoadById($groupId);

        $expectedGroupCommand = new GetGroupByIdCommand($groupId);
        $expectedUsersCommand = new GetAllGroupUsersCommand($groupId, AccountStatus::ACTIVE);
        $expectedPermissionsCommand = new GetAllGroupPermissionsCommand($groupId);
        $expectedRolesCommand = new GetAllGroupRolesCommand($groupId);

        $this->assertTrue($this->db->ContainsCommand($expectedGroupCommand));
        $this->assertTrue($this->db->ContainsCommand($expectedUsersCommand));
        $this->assertTrue($this->db->ContainsCommand($expectedPermissionsCommand));
        $this->assertTrue($this->db->ContainsCommand($expectedRolesCommand));
        $this->assertEquals($groupId, $group->Id());
        $this->assertEquals($groupName, $group->Name());
        $this->assertEquals($groupAdminId, $group->AdminGroupId());
        $this->assertTrue($group->HasMember(1));
        $this->assertFalse($group->HasMember(3));
        $this->assertTrue(in_array(1, $group->AllowedResourceIds()));
        $this->assertFalse(in_array(3, $group->AllowedResourceIds()));
        $this->assertTrue(in_array(2, $group->RoleIds()));
        $this->assertFalse(in_array(4, $group->RoleIds()));
    }

    public function testUpdateRemovesAllUsersMarked()
    {
        $user1 = 100;
        $user2 = 200;
        $groupId = 9298;

        $group = new Group($groupId, '');
        $group->WithUser($user1);
        $group->WithUser($user2);

        $group->RemoveUser($user1);
        $group->RemoveUser($user2);

        $this->repository->Update($group);

        $removeCommand1 = new DeleteUserGroupCommand($user1, $groupId);
        $removeCommand2 = new DeleteUserGroupCommand($user2, $groupId);

        $this->assertTrue($this->db->ContainsCommand($removeCommand1));
        $this->assertTrue($this->db->ContainsCommand($removeCommand2));
    }

    public function testUpdateAddsAllUsersMarked()
    {
        $user1 = 100;
        $user2 = 200;
        $groupId = 9298;

        $group = new Group($groupId, '');

        $group->AddUser($user1);
        $group->AddUser($user2);

        $this->repository->Update($group);

        $command1 = new AddUserGroupCommand($user1, $groupId);
        $command2 = new AddUserGroupCommand($user2, $groupId);

        $this->assertTrue($this->db->ContainsCommand($command1));
        $this->assertTrue($this->db->ContainsCommand($command2));
    }

    public function testUpdateAddsAllNewAndRemovesAllDeletedPermissions()
    {
        $resource1 = 100;
        $resource2 = 200;
        $resource3 = 300;

        $groupId = 9298;

        $group = new Group($groupId, '');
        $group->WithFullPermission($resource1);
        $group->WithFullPermission($resource3);
        $group->WithViewablePermission($resource2);
        $group->ChangeAllowedPermissions(array($resource2));
        $group->ChangeViewPermissions(array($resource1));

        $this->repository->Update($group);

        $removeCommand1 = new DeleteGroupResourcePermission($groupId, $resource1);
        $removeCommand2 = new DeleteGroupResourcePermission($groupId, $resource2);
        $removeCommand3 = new DeleteGroupResourcePermission($groupId, $resource3);
        $addGroup1 = new AddGroupResourcePermission($groupId, $resource2, ResourcePermissionType::Full);
        $addGroup2 = new AddGroupResourcePermission($groupId, $resource1, ResourcePermissionType::View);

        $this->assertTrue($this->db->ContainsCommand($removeCommand1));
        $this->assertTrue($this->db->ContainsCommand($removeCommand2));
        $this->assertTrue($this->db->ContainsCommand($removeCommand3));
        $this->assertTrue($this->db->ContainsCommand($addGroup1));
        $this->assertTrue($this->db->ContainsCommand($addGroup2));
    }

    public function testUpdateSavesNewGroupNameAndAdminId()
    {
        $id = 2828;
        $newName = 'new name';
        $groupAdminId = 123;
        $isDefault = 1;

        $group = new Group($id, 'old name', $isDefault);
        $group->Rename($newName);
        $group->ChangeAdmin($groupAdminId);

        $this->repository->Update($group);

        $updateGroupCommand = new UpdateGroupCommand($id, $newName, $groupAdminId, $isDefault);
        $this->assertTrue($this->db->ContainsCommand($updateGroupCommand));
    }

    public function testRemoveDeletesGroupFromDatabase()
    {
        $id = 123;
        $group = new Group($id, '');
        $this->repository->Remove($group);

        $deleteGroupCommand = new DeleteGroupCommand($id);
        $this->assertTrue($this->db->ContainsCommand($deleteGroupCommand));
    }

    public function testCanAddNewGroup()
    {
        $newId = 40298;
        $name = 'gn';
        $isDefault = 1;
        $group = new Group(0, $name, $isDefault);

        $this->db->_ExpectedInsertId = $newId;

        $this->repository->Add($group);

        $addGroupCommand = new AddGroupCommand($name, $isDefault);
        $this->assertTrue($this->db->ContainsCommand($addGroupCommand));

        $this->assertEquals($newId, $group->Id());
    }

    public function testUpdateAddsAllNewAndRemovesAllDeletedRoles()
    {
        $roleId1 = 100;
        $roleId2 = 200;
        $roleId3 = 300;

        $groupId = 9298;

        $group = new Group($groupId, '');
        $group->WithRole($roleId1);
        $group->WithRole($roleId3);

        $group->ChangeRoles(array($roleId2, $roleId3));

        $this->repository->Update($group);

        $removeCommand = new DeleteGroupRoleCommand($groupId, $roleId1);
        $addCommand = new AddGroupRoleCommand($groupId, $roleId2);

        $this->assertTrue($this->db->ContainsCommand($removeCommand));
        $this->assertTrue($this->db->ContainsCommand($addCommand));
    }

    public function testCanGetListOfGroupsByRole()
    {
        $roleLevel = RoleLevel::GROUP_ADMIN;
        $groupItemRow = new GroupItemRow();
        $groupItemRow->With(1, 'g1')->With(2, 'g2');

        $rows = $groupItemRow->Rows();
        $this->db->SetRow(0, $rows);

        $getGroupsCommand = new GetAllGroupsByRoleCommand($roleLevel);
        $groups = $this->repository->GetGroupsByRole($roleLevel);

        $this->assertEquals(GroupItemView::Create($rows[0]), $groups[0]);
        $this->assertEquals(GroupItemView::Create($rows[1]), $groups[1]);

        $this->assertTrue($this->db->ContainsCommand($getGroupsCommand), "missing select group command");
    }

    private function GetGroupUserRow($userId, $firstName, $lastName)
    {
        return array(
            ColumnNames::USER_ID => $userId,
            ColumnNames::FIRST_NAME => $firstName,
            ColumnNames::LAST_NAME => $lastName,
            ColumnNames::USERNAME => 'username',
            ColumnNames::EMAIL => 'email',
            ColumnNames::LAST_LOGIN => null,
            ColumnNames::LANGUAGE_CODE => 'en_us',
            ColumnNames::TIMEZONE_NAME => 'America/Chicago',
            ColumnNames::USER_STATUS_ID => AccountStatus::ACTIVE,
            ColumnNames::PASSWORD => 'encryptedPassword',
            ColumnNames::SALT => 'passwordsalt',
            ColumnNames::HOMEPAGE_ID => 3,
            ColumnNames::PHONE_NUMBER => '123-456-7890',
            ColumnNames::POSITION => 'head honcho',
            ColumnNames::ORGANIZATION => 'earth',
            ColumnNames::USER_CREATED => '2011-01-04 12:12:12',
        );
    }
}

?>