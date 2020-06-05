<?php

/**
 * Copyright 2018-2020 Nick Korbel
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

require_once(ROOT_DIR . 'lib/WebService/namespace.php');
require_once(ROOT_DIR . 'WebServices/Controllers/GroupSaveController.php');
require_once(ROOT_DIR . 'WebServices/Responses/Group/GroupCreatedResponse.php');
require_once(ROOT_DIR . 'WebServices/Requests/Group/GroupRequest.php');

class GroupsWriteWebService
{
	/**
	 * @var IGroupSaveController
	 */
	private $controller;

	/**
	 * @var IRestServer
	 */
	private $server;

	public function __construct(IRestServer $server, IGroupSaveController $controller)
	{
		$this->server = $server;
		$this->controller = $controller;
	}

	/**
	 * @name CreateGroup
	 * @description Creates a new group
	 * @request GroupRequest
	 * @response GroupCreatedResponse
	 * @return void
	 */
	public function Create()
	{
		/** @var $request GroupRequest */
		$request = $this->server->GetRequest();

		Log::Debug('GroupsWriteWebService.Create() User=%s, Request=%s', $this->server->GetSession()->UserId, json_encode($request));

		$result = $this->controller->Create($request, $this->server->GetSession());

		if ($result->WasSuccessful())
		{
			Log::Debug('GroupsWriteWebService.Create() - Group Created. GroupId=%s', $result->GroupId());

			$this->server->WriteResponse(new GroupCreatedResponse($this->server, $result->GroupId()), RestResponse::CREATED_CODE);
		}
		else
		{
			Log::Debug('GroupsWriteWebService.Create() - Create Failed.');

			$this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
		}
	}

	/**
	 * @name UpdateGroup
	 * @description Updates and existing group
	 * @request GroupRequest
	 * @response GroupUpdatedResponse
	 * @param $groupId
	 * @return void
	 */
	public function Update($groupId)
	{
		/** @var $request GroupRequest */
		$request = $this->server->GetRequest();

		Log::Debug('GroupsWriteWebService.Update() User=%s, GroupId=%s, Request=%s', $this->server->GetSession()->UserId, $groupId, json_encode($request));

		$result = $this->controller->Update($groupId, $request, $this->server->GetSession());

		if ($result->WasSuccessful())
		{
			Log::Debug('GroupsWriteWebService.Update() - Group Updated. GroupId=%s', $result->GroupId());

			$this->server->WriteResponse(new GroupUpdatedResponse($this->server, $result->GroupId()), RestResponse::CREATED_CODE);
		}
		else
		{
			Log::Debug('GroupsWriteWebService.Update() - Update Failed.');

			$this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
		}
	}

	/**
	 * @name DeleteGroup
	 * @description Deletes an existing group
	 * @response DeletedResponse
	 * @param int $groupId
	 * @return void
	 */
	public function Delete($groupId)
	{
		Log::Debug('GroupsWriteWebService.Delete() GroupId=%s, UserId=%s', $groupId, $this->server->GetSession()->UserId);

		$result = $this->controller->Delete($groupId, $this->server->GetSession());

		if ($result->WasSuccessful())
		{
			Log::Debug('GroupsWriteWebService.Delete() - Group Deleted. GroupId=%s', $result->GroupId());

			$this->server->WriteResponse(new DeletedResponse(), RestResponse::OK_CODE);
		}
		else
		{
			Log::Debug('GroupsWriteWebService.Delete() - Group Delete Failed.');

			$this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
		}
	}

    /**
     * @name ChangeGroupRoles
     * @description Updates the roles for an existing group
     * roleIds : 1 (Group Administrator), 2 (Application Administrator), 3 (Resource Administrator), 4 (Schedule Administrator)
     * @response GroupUpdatedResponse
     * @param int $groupId
     * @return void
     */
	public function Roles($groupId)
    {
        /** @var $request GroupRolesRequest */
        $request = $this->server->GetRequest();

        Log::Debug('GroupsWriteWebService.Roles() User=%s, GroupId=%s, Request=%s', $this->server->GetSession()->UserId, $groupId, json_encode($request));

        $result = $this->controller->ChangeRoles($groupId, $request, $this->server->GetSession());

        if ($result->WasSuccessful())
        {
            Log::Debug('GroupsWriteWebService.Roles() - Group Updated. GroupId=%s', $result->GroupId());

            $this->server->WriteResponse(new GroupUpdatedResponse($this->server, $result->GroupId()), RestResponse::CREATED_CODE);
        }
        else
        {
            Log::Debug('GroupsWriteWebService.Roles() - Update Failed.');

            $this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
        }
    }

    /**
     * @name ChangeGroupPermissions
     * @description Updates the permissions for an existing group
     * @response GroupUpdatedResponse
     * @param int $groupId
     * @return void
     */
	public function Permissions($groupId)
    {
        /** @var $request GroupPermissionsRequest */
        $request = $this->server->GetRequest();

        Log::Debug('GroupsWriteWebService.Permissions() User=%s, GroupId=%s, Request=%s', $this->server->GetSession()->UserId, $groupId, json_encode($request));

        $result = $this->controller->ChangePermissions($groupId, $request, $this->server->GetSession());

        if ($result->WasSuccessful())
        {
            Log::Debug('GroupsWriteWebService.Permissions() - Group Updated. GroupId=%s', $result->GroupId());

            $this->server->WriteResponse(new GroupUpdatedResponse($this->server, $result->GroupId()), RestResponse::CREATED_CODE);
        }
        else
        {
            Log::Debug('GroupsWriteWebService.Permissions() - Update Failed.');

            $this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
        }
    }

    /**
     * @name ChangeGroupUsers
     * @description Updates the permissions for an existing group
     * @response GroupUpdatedResponse
     * @param int $groupId
     * @return void
     */
	public function Users($groupId)
    {
        /** @var $request GroupUsersRequest */
        $request = $this->server->GetRequest();

        Log::Debug('GroupsWriteWebService.Users() User=%s, GroupId=%s, Request=%s', $this->server->GetSession()->UserId, $groupId, json_encode($request));

        $result = $this->controller->ChangeUsers($groupId, $request, $this->server->GetSession());

        if ($result->WasSuccessful())
        {
            Log::Debug('GroupsWriteWebService.Users() - Group Updated. GroupId=%s', $result->GroupId());

            $this->server->WriteResponse(new GroupUpdatedResponse($this->server, $result->GroupId()), RestResponse::CREATED_CODE);
        }
        else
        {
            Log::Debug('GroupsWriteWebService.Users() - Update Failed.');

            $this->server->WriteResponse(new FailedResponse($this->server, $result->Errors()), RestResponse::BAD_REQUEST_CODE);
        }
    }
}
