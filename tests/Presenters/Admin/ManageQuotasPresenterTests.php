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

require_once(ROOT_DIR . 'Pages/Admin/ManageQuotasPage.php');

class ManageQuotasPresenterTests extends TestBase
{
	/**
	 * @var IManageQuotasPage|PHPUnit_Framework_MockObject_MockObject
	 */
	private $page;

	/**
	 * @var ManageQuotasPresenter
	 */
	private $presenter;

	/**
	 * @var IResourceRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $resourceRepository;

	/**
	 * @var IGroupViewRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $groupRepository;

	/**
	 * @var IScheduleRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $scheduleRepository;

	/**
	 * @var QuotaRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	public $quotaRepository;

	public function setUp(): void
	{
		parent::setup();

		$this->page = $this->createMock('IManageQuotasPage');
		$this->resourceRepository = $this->createMock('IResourceRepository');
		$this->groupRepository = $this->createMock('IGroupViewRepository');
		$this->scheduleRepository = $this->createMock('IScheduleRepository');
		$this->quotaRepository = $this->createMock('QuotaRepository');

		$this->presenter = new ManageQuotasPresenter(
				$this->page,
				$this->resourceRepository,
				$this->groupRepository,
				$this->scheduleRepository,
				$this->quotaRepository);
	}

	public function teardown(): void
	{
		parent::teardown();
	}

	public function testWhenInitializing()
	{
		$groups = array();
		$bookableResources = array();
		$schedules = array();

		$groupResult = new PageableData($groups);

		$quotaList = array();

		$this->resourceRepository->expects($this->once())
								 ->method('GetResourceList')
								 ->will($this->returnValue($bookableResources));

		$this->page->expects($this->once())
				   ->method('BindResources')
				   ->with($this->equalTo($bookableResources));

		$this->groupRepository->expects($this->once())
							  ->method('GetList')
							  ->will($this->returnValue($groupResult));

		$this->page->expects($this->once())
				   ->method('BindGroups')
				   ->with($this->equalTo($groups));

		$this->scheduleRepository->expects($this->once())
								 ->method('GetAll')
								 ->will($this->returnValue($schedules));

		$this->page->expects($this->once())
				   ->method('BindSchedules')
				   ->with($this->equalTo($schedules));

		$this->quotaRepository->expects($this->once())
							  ->method('GetAll')
							  ->will($this->returnValue($quotaList));

		$this->presenter->PageLoad();
	}

	public function testWhenAdding()
	{
		$duration = QuotaDuration::Day;
		$limit = 2;
		$unit = QuotaUnit::Hours;
		$resourceId = 987;
		$groupId = 8287;
		$scheduleId = 400;
		$enforcedStartTime = '10:00am';
		$enforcedEndTime = '4:30pm';
		$enforcedDays = array(1, 3, 5);
		$scope = QuotaScope::ExcludeCompleted;

		$this->page->expects($this->atLeastOnce())
				   ->method('GetDuration')
				   ->will($this->returnValue($duration));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetLimit')
				   ->will($this->returnValue($limit));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetUnit')
				   ->will($this->returnValue($unit));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetResourceId')
				   ->will($this->returnValue($resourceId));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetGroupId')
				   ->will($this->returnValue($groupId));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetScheduleId')
				   ->will($this->returnValue($scheduleId));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetEnforcedAllDay')
				   ->will($this->returnValue(false));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetEnforcedStartTime')
				   ->will($this->returnValue($enforcedStartTime));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetEnforcedEndTime')
				   ->will($this->returnValue($enforcedEndTime));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetEnforcedEveryDay')
				   ->will($this->returnValue(false));

		$this->page->expects($this->atLeastOnce())
				   ->method('GetEnforcedDays')
				   ->will($this->returnValue($enforcedDays));

		$this->page->expects($this->atLeastOnce())
						->method('GetScope')
						->will($this->returnValue($scope));

		$expectedQuota = Quota::Create($duration, $limit, $unit, $resourceId, $groupId, $scheduleId, $enforcedStartTime, $enforcedEndTime, $enforcedDays, $scope);

		$this->quotaRepository->expects($this->once())
							  ->method('Add')
							  ->with($this->equalTo($expectedQuota));

		$this->presenter->AddQuota();
	}
}