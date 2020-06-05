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

require_once(ROOT_DIR . 'Presenters/Admin/ManageBlackoutsPresenter.php');

class ManageBlackoutsPresenterTests extends TestBase
{
	/**
	 * @var ManageBlackoutsPresenter
	 */
	private $presenter;

	/**
	 * @var IManageBlackoutsPage|PHPUnit_Framework_MockObject_MockObject
	 */
	private $page;

	/**
	 * @var IManageBlackoutsService|PHPUnit_Framework_MockObject_MockObject
	 */
	private $blackoutsService;

	/**
	 * @var IScheduleRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $scheduleRepository;

	/**
	 * @var IResourceRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $resourceRepository;

	public function setUp(): void
	{
		parent::setup();

		$this->page = $this->createMock('IManageBlackoutsPage');
		$this->blackoutsService = $this->createMock('IManageBlackoutsService');
		$this->scheduleRepository = $this->createMock('IScheduleRepository');
		$this->resourceRepository = $this->createMock('IResourceRepository');

		$this->presenter = new ManageBlackoutsPresenter($this->page,
											$this->blackoutsService,
											$this->scheduleRepository,
											$this->resourceRepository);
	}

	public function testUsesTwoWeekSpanWhenNoDateFilterProvided()
	{
		$userTimezone = $this->fakeUser->Timezone;
		$defaultStart = Date::Now()->AddDays(-7)->ToTimezone($userTimezone)->GetDate();
		$defaultEnd = Date::Now()->AddDays(7)->ToTimezone($userTimezone)->GetDate();
		$searchedScheduleId = 15;
		$searchedResourceId = 105;

		$this->page->expects($this->atLeastOnce())
				->method('GetStartDate')
				->will($this->returnValue(null));

		$this->page->expects($this->atLeastOnce())
				->method('GetEndDate')
				->will($this->returnValue(null));

		$this->page->expects($this->once())
			->method('GetScheduleId')
			->will($this->returnValue($searchedScheduleId));

		$this->page->expects($this->once())
			->method('GetResourceId')
			->will($this->returnValue($searchedResourceId));

		$filter = $this->GetExpectedFilter($defaultStart, $defaultEnd, $searchedScheduleId, $searchedResourceId);
		$data = new PageableData();
		$this->blackoutsService->expects($this->once())
				->method('LoadFiltered')
				->with($this->anything(), $this->anything(), $this->anything(), $this->anything(), $this->equalTo($filter), $this->equalTo($this->fakeUser))
				->will($this->returnValue($data));

		$this->page->expects($this->once())
				->method('SetStartDate')
				->with($this->equalTo($defaultStart));

		$this->page->expects($this->once())
				->method('SetEndDate')
				->with($this->equalTo($defaultEnd));

		$this->page->expects($this->once())
			->method('SetScheduleId')
			->with($this->equalTo($searchedScheduleId));

		$this->page->expects($this->once())
			->method('SetResourceId')
			->with($this->equalTo($searchedResourceId));

		$this->presenter->PageLoad($userTimezone);
	}

	public function testAddsNewBlackoutTimeForSingleResource()
	{
		$startDate = '1/1/2011';
		$endDate = '1/2/2011';
		$startTime = '01:30 PM';
		$endTime = '12:15 AM';
		$timezone = $this->fakeUser->Timezone;
		$dr = DateRange::Create($startDate . ' ' . $startTime, $endDate . ' ' . $endTime, $timezone);
		$title = 'out of service';
		$conflictAction = ReservationConflictResolution::Delete;
		$conflictResolution = ReservationConflictResolution::Create($conflictAction);
		$endDateString = '2012-01-01';
		$repeatType = RepeatType::Daily;
		$repeatInterval = 1;
		$repeatDays = array(1, 2);
		$repeatMonthlyType = RepeatMonthlyType::DayOfMonth;

		$roFactory = new RepeatOptionsFactory();
		$repeatEndDate = Date::Parse($endDateString, $timezone);
		$repeatOptions = $roFactory->Create($repeatType, $repeatInterval, $repeatEndDate, $repeatDays, $repeatMonthlyType, []);

		$this->ExpectPageToReturnCommonBlackoutInfo($startDate, $startTime, $endDate, $endTime, $title, $conflictAction);
		$this->ExpectPageToReturnRepeatInfo($repeatType, $repeatInterval, $endDateString, $repeatDays, $repeatMonthlyType);

		$resourceId = 123;
		$this->page->expects($this->once())
			->method('GetBlackoutResourceId')
			->will($this->returnValue($resourceId));

		$this->page->expects($this->once())
			->method('GetApplyBlackoutToAllResources')
			->will($this->returnValue(false));

		$result = $this->createMock('IBlackoutValidationResult');
		$this->blackoutsService->expects($this->once())
			->method('Add')
			->with($this->equalTo($dr),
				   $this->equalTo(array($resourceId)),
				   $this->equalTo($title),
				   $this->equalTo($conflictResolution),
				$this->equalTo($repeatOptions))
			->will($this->returnValue($result));

		$this->presenter->AddBlackout();
	}

	public function testAddsNewBlackoutTimeForSchedule()
	{
		$startDate = '1/1/2011';
		$endDate = '1/2/2011';
		$startTime = '01:30 PM';
		$endTime = '12:15 AM';
		$timezone = $this->fakeUser->Timezone;
		$dr = DateRange::Create($startDate . ' ' . $startTime, $endDate . ' ' . $endTime, $timezone);
		$title = 'out of service';
		$conflictAction = ReservationConflictResolution::Delete;
		$conflictResolution = ReservationConflictResolution::Create($conflictAction);

		$endDateString = '2012-01-01';
		$repeatType = RepeatType::Daily;
		$repeatInterval = 1;
		$repeatDays = array(1, 2);
		$repeatMonthlyType = RepeatMonthlyType::DayOfMonth;

		$roFactory = new RepeatOptionsFactory();
		$repeatEndDate = Date::Parse($endDateString, $timezone);
		$repeatOptions = $roFactory->Create($repeatType, $repeatInterval, $repeatEndDate, $repeatDays, $repeatMonthlyType, []);

		$this->ExpectPageToReturnCommonBlackoutInfo($startDate, $startTime, $endDate, $endTime, $title, $conflictAction);
		$this->ExpectPageToReturnRepeatInfo($repeatType, $repeatInterval, $endDateString, $repeatDays, $repeatMonthlyType);

		$scheduleId = 123;
		$this->page->expects($this->once())
			->method('GetBlackoutScheduleId')
			->will($this->returnValue($scheduleId));

		$this->page->expects($this->once())
			->method('GetApplyBlackoutToAllResources')
			->will($this->returnValue(true));

		$resources = array(new FakeBookableResource(1), new FakeBookableResource(2), new FakeBookableResource(3));
		$this->resourceRepository->expects($this->once())
			->method('GetScheduleResources')
			->with($this->equalTo($scheduleId))
			->will($this->returnValue($resources));

		$result = $this->createMock('IBlackoutValidationResult');
		$this->blackoutsService->expects($this->once())
			->method('Add')
			->with($this->equalTo($dr),
				   $this->equalTo(array(1, 2, 3)),
				   $this->equalTo($title),
				   $this->equalTo($conflictResolution),
				$this->equalTo($repeatOptions))
			->will($this->returnValue($result));

		$this->presenter->AddBlackout();
	}

    public function testDeletesBlackoutById()
    {
        $id = 123;
		$scope = SeriesUpdateScope::ThisInstance;

        $this->page->expects($this->once())
        			->method('GetBlackoutId')
        			->will($this->returnValue($id));

		$this->page->expects($this->once())
		        			->method('GetSeriesUpdateScope')
		        			->will($this->returnValue(SeriesUpdateScope::ThisInstance));

        $this->blackoutsService->expects($this->once())
        			->method('Delete')
        			->with($this->equalTo($id), $this->equalTo($scope));

        $this->presenter->DeleteBlackout();
    }

	public function testLoadsBlackoutSeriesByBlackoutId()
	{
		$series = BlackoutSeries::Create(1, 'title', new TestDateRange());
		$repeatOptions = new RepeatDaily(1, Date::Now());
		$series->WithRepeatOptions($repeatOptions);
		$series->AddResource(new BlackoutResource(1, 'r1', 1));
		$series->AddResource(new BlackoutResource(2, 'r2', 1));
		$config = $series->RepeatConfiguration();

		$userTz = $this->fakeUser->Timezone;

		$id = 123;

		$this->page->expects($this->once())
				   ->method('GetBlackoutId')
				   ->will($this->returnValue($id));

		$this->page->expects($this->once())
					->method('SetBlackoutResources')
					->with($this->equalTo($series->ResourceIds()));

		$this->page->expects($this->once())
					->method('SetTitle')
					->with($this->equalTo('title'));

		$this->page->expects($this->once())
					->method('SetRepeatType')
					->with($this->equalTo($config->Type));

		$this->page->expects($this->once())
					->method('SetRepeatInterval')
					->with($this->equalTo($config->Interval));

		$this->page->expects($this->once())
					->method('SetRepeatMonthlyType')
					->with($this->equalTo($config->MonthlyType));

		$this->page->expects($this->once())
					->method('SetRepeatWeekdays')
					->with($this->equalTo($config->Weekdays));

		$this->page->expects($this->once())
					->method('SetRepeatTerminationDate')
					->with($this->equalTo($config->TerminationDate->ToTimezone($userTz)));

		$this->page->expects($this->once())
					->method('SetBlackoutId')
					->with($this->equalTo($id));

		$this->page->expects($this->once())
					->method('SetIsRecurring')
					->with($this->equalTo(true));

		$this->page->expects($this->once())
					->method('SetBlackoutStartDate')
					->with($this->equalTo($series->CurrentBlackout()->StartDate()->ToTimezone($userTz)));

		$this->page->expects($this->once())
					->method('SetBlackoutEndDate')
					->with($this->equalTo($series->CurrentBlackout()->EndDate()->ToTimezone($userTz)));

		$this->page->expects($this->once())
							->method('SetWasBlackoutFound')
							->with($this->equalTo(true));

		$this->page->expects($this->once())
							->method('ShowBlackout');

		$this->blackoutsService->expects($this->once())
							   ->method('LoadBlackout')
							   ->with($this->equalTo($id), $this->equalTo($this->fakeUser->UserId))
							   ->will($this->returnValue($series));

		$this->presenter->LoadBlackout();
	}

	public function testUpdatesBlackout()
	{
		$startDate = '1/1/2011';
		$endDate = '1/2/2011';
		$startTime = '01:30 PM';
		$endTime = '12:15 AM';
		$timezone = $this->fakeUser->Timezone;
		$dr = DateRange::Create($startDate . ' ' . $startTime, $endDate . ' ' . $endTime, $timezone);
		$title = 'out of service';
		$conflictAction = ReservationConflictResolution::Delete;
		$conflictResolution = ReservationConflictResolution::Create($conflictAction);
		$endDateString = '2012-01-01';
		$repeatType = RepeatType::Daily;
		$repeatInterval = 1;
		$repeatDays = array(1, 2);
		$repeatMonthlyType = RepeatMonthlyType::DayOfMonth;
		$blackoutInstanceId = 1111;
		$scope = SeriesUpdateScope::ThisInstance;

		$roFactory = new RepeatOptionsFactory();
		$repeatEndDate = Date::Parse($endDateString, $timezone);
		$repeatOptions = $roFactory->Create($repeatType, $repeatInterval, $repeatEndDate, $repeatDays, $repeatMonthlyType, []);

		$this->ExpectPageToReturnCommonBlackoutInfo($startDate, $startTime, $endDate, $endTime, $title, $conflictAction);
		$this->ExpectPageToReturnRepeatInfo($repeatType, $repeatInterval, $endDateString, $repeatDays, $repeatMonthlyType);

		$resourceIds = array(123, 456);
		$this->page->expects($this->once())
			->method('GetBlackoutResourceIds')
			->will($this->returnValue($resourceIds));

		$this->page->expects($this->once())
			->method('GetUpdateBlackoutId')
			->will($this->returnValue($blackoutInstanceId));

		$this->page->expects($this->once())
			->method('GetSeriesUpdateScope')
			->will($this->returnValue($scope));

		$result = $this->createMock('IBlackoutValidationResult');

		$this->blackoutsService->expects($this->once())
			->method('Update')
			->with($this->equalTo($blackoutInstanceId),
				   $this->equalTo($dr),
				   $this->equalTo($resourceIds),
				   $this->equalTo($title),
				   $this->equalTo($conflictResolution),
				   $this->equalTo( $repeatOptions),
				   $this->equalTo($scope))
			->will($this->returnValue($result));

		$this->presenter->UpdateBlackout();
	}

	/**
	 * @param Date $startDate
	 * @param Date $endDate
	 * @param int $scheduleId
	 * @param int $resourceId
	 * @return BlackoutFilter
	 */
	private function GetExpectedFilter($startDate = null, $endDate = null, $scheduleId = null, $resourceId = null)
	{
		return new BlackoutFilter($startDate, $endDate, $scheduleId, $resourceId);
	}

	private function ExpectPageToReturnCommonBlackoutInfo($startDate, $startTime, $endDate, $endTime, $title, $conflictAction)
	{
		$this->page->expects($this->once())
			->method('GetBlackoutStartDate')
			->will($this->returnValue($startDate));

		$this->page->expects($this->once())
			->method('GetBlackoutStartTime')
			->will($this->returnValue($startTime));

		$this->page->expects($this->once())
			->method('GetBlackoutEndDate')
			->will($this->returnValue($endDate));

		$this->page->expects($this->once())
			->method('GetBlackoutEndTime')
			->will($this->returnValue($endTime));

		$this->page->expects($this->once())
			->method('GetBlackoutTitle')
			->will($this->returnValue($title));

		$this->page->expects($this->once())
			->method('GetBlackoutConflictAction')
			->will($this->returnValue($conflictAction));
	}

	private function ExpectPageToReturnRepeatInfo($repeatType = RepeatType::None, $repeatInterval = null, $endDateString = null, $repeatDays = null, $repeatMonthlyType = null)
	{
		$this->page->expects($this->any())
					->method('GetRepeatType')
					->will($this->returnValue($repeatType));

		$this->page->expects($this->any())
					->method('GetRepeatInterval')
					->will($this->returnValue($repeatInterval));

		$this->page->expects($this->any())
					->method('GetRepeatTerminationDate')
					->will($this->returnValue($endDateString));

		$this->page->expects($this->any())
					->method('GetRepeatWeekdays')
					->will($this->returnValue($repeatDays));

		$this->page->expects($this->any())
					->method('GetRepeatMonthlyType')
					->will($this->returnValue($repeatMonthlyType));

		$this->page->expects($this->once())
					->method('GetRepeatCustomDates')
					->will($this->returnValue([]));
	}
}