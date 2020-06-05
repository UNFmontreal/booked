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

require_once(ROOT_DIR . 'lib/Application/Admin/namespace.php');

class ResourceAdminManageReservationsServiceTests extends TestBase
{
	/**
	 * @var IReservationViewRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $reservationViewRepository;

	/**
	 * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $userRepository;

	/**
	 * @var IReservationAuthorization|PHPUnit_Framework_MockObject_MockObject
	 */
	private $reservationAuthorization;

	/**
	 * @var ManageReservationsService
	 */
	private $service;

	public function setUp(): void
	{
		parent::setup();

		$this->reservationViewRepository = $this->createMock('IReservationViewRepository');
		$this->userRepository = $this->createMock('IUserRepository');
		$this->reservationAuthorization = $this->createMock('IReservationAuthorization');
		$handler = $this->createMock('IReservationHandler');
		$persistenceService = $this->createMock('IUpdateReservationPersistenceService');

		$this->service = new ResourceAdminManageReservationsService($this->reservationViewRepository, $this->userRepository, $this->reservationAuthorization, $handler, $persistenceService);
	}

	public function testLoadsFilteredResultsAndChecksAuthorizationAgainstPendingReservations()
	{
		$pageNumber = 1;
		$pageSize = 40;

		$groups = array(
			new UserGroup(1, '1'),
			new UserGroup(5, '5'),
			new UserGroup(9, '9'),
			new UserGroup(22, '22'),
		);
		$myGroups = array(1, 5, 9, 22);

		$this->userRepository->expects($this->once())
					->method('LoadGroups')
					->with($this->equalTo($this->fakeUser->UserId), $this->equalTo(RoleLevel::RESOURCE_ADMIN))
					->will($this->returnValue($groups));

		$filter = new ReservationFilter();
		$expectedFilter = $filter->GetFilter();
		$expectedFilter->_And(new SqlFilterIn(new SqlFilterColumn(TableNames::RESOURCES, ColumnNames::RESOURCE_ADMIN_GROUP_ID), $myGroups));

		$data = new PageableData();
		$this->reservationViewRepository->expects($this->once())
				->method('GetList')
				->with($pageNumber, $pageSize, null, null, $expectedFilter)
				->will($this->returnValue($data));

		$actualData = $this->service->LoadFiltered($pageNumber, $pageSize, null, null, $filter, $this->fakeUser);

		$this->assertEquals($data, $actualData);
	}
}