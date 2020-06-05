<?php
/**
Copyright 2012-2020 Nick Korbel

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

require_once(ROOT_DIR . 'WebServices/ReservationsWebService.php');

class ReservationsWebServiceTests extends TestBase
{
	/**
	 * @var FakeRestServer
	 */
	private $server;

	/**
	 * @var ReservationsWebService
	 */
	private $service;

	/**
	 * @var IReservationViewRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $reservationViewRepository;

	/**
	 * @var IPrivacyFilter
	 */
	private $privacyFilter;

	/**
	 * @var IAttributeService
	 */
	private $attributeService;

	/**
	 * @var WebServiceUserSession
	 */
	private $userSession;

	/**
	 * @var Date
	 */
	private $defaultStartDate;

	/**
	 * @var Date
	 */
	private $defaultEndDate;

	public function setUp(): void
	{
		parent::setup();

		$this->userSession = new WebServiceUserSession(123);
		$this->userSession->Timezone = 'America/Chicago';

		$this->defaultStartDate = Date::Now()->AddDays(0);
		$this->defaultEndDate = Date::Now()->AddDays(14);

		$this->server = new FakeRestServer();
		$this->server->SetSession($this->userSession);

		$this->reservationViewRepository = $this->createMock('IReservationViewRepository');
		$this->privacyFilter = $this->createMock('IPrivacyFilter');
		$this->attributeService = $this->createMock('IAttributeService');

		$this->service = new ReservationsWebService(
			$this->server,
			$this->reservationViewRepository,
			$this->privacyFilter,
			$this->attributeService);
	}

	public function testDefaultsToNextTwoWeeks()
	{
		$this->server->SetQueryString(WebServiceQueryStringKeys::USER_ID, null);
		$this->server->SetQueryString(WebServiceQueryStringKeys::START_DATE_TIME, null);
		$this->server->SetQueryString(WebServiceQueryStringKeys::END_DATE_TIME, null);

		$reservations = array();

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservations')
				->with($this->equalTo($this->defaultStartDate), $this->equalTo($this->defaultEndDate))
				->will($this->returnValue($reservations));

		$this->service->GetReservations();

		$expectedResponse = new ReservationsResponse($this->server, $reservations, $this->privacyFilter, $this->defaultStartDate, $this->defaultEndDate);
		$this->assertEquals($expectedResponse, $this->server->_LastResponse);
	}

	public function testWhenUserIdIsForAnotherUser()
	{
		$userId = 9999;
		$user = new User();
		$user->WithId($userId);

		$this->server->SetQueryString(WebServiceQueryStringKeys::USER_ID, $userId);

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservations')
				->with($this->anything(), $this->anything(), $this->equalTo($userId))
				->will($this->returnValue(array()));

		$this->service->GetReservations();
	}

	public function testWhenResourceIdIsProvided()
	{
		$resourceId = 12345;

		$this->server->SetQueryString(WebServiceQueryStringKeys::RESOURCE_ID, $resourceId);

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservations')
				->with($this->equalTo($this->defaultStartDate), $this->equalTo($this->defaultEndDate),
					   $this->isNull(), $this->isNull(),
					   $this->isNull(), $this->equalTo($resourceId))
				->will($this->returnValue(array()));

		$this->service->GetReservations();
	}

	public function testWhenScheduleIdIsProvided()
	{
		$scheduleId = 12346;

		$this->server->SetQueryString(WebServiceQueryStringKeys::SCHEDULE_ID, $scheduleId);

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservations')
				->with($this->equalTo($this->defaultStartDate), $this->equalTo($this->defaultEndDate),
					   $this->isNull(), $this->isNull(),
					   $this->equalTo($scheduleId), $this->isNull())
				->will($this->returnValue(array()));

		$this->service->GetReservations();
	}

	public function testLoadsASingleReservation()
	{
		$referenceNumber = '12323';
		$reservation = new ReservationView();
		$reservation->StartDate = Date::Now();
		$reservation->EndDate = Date::Now();
		$reservation->ReferenceNumber = $referenceNumber;
		$attributes = array(new FakeCustomAttribute());

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservationForEditing')
				->with($this->equalTo($referenceNumber))
				->will($this->returnValue($reservation));

		$this->attributeService->expects($this->once())
				->method('GetByCategory')
				->with($this->equalTo(CustomAttributeCategory::RESERVATION))
				->will($this->returnValue($attributes));

		$this->service->GetReservation($referenceNumber);

		$expectedResponse = new ReservationResponse($this->server, $reservation, $this->privacyFilter, $attributes);

		$this->assertEquals($expectedResponse, $this->server->_LastResponse);
	}

	public function testWhenReservationIsNotFound()
	{
		$reservation = NullReservationView::Instance();

		$referenceNumber = '12323';

		$this->reservationViewRepository->expects($this->once())
				->method('GetReservationForEditing')
				->with($this->equalTo($referenceNumber))
				->will($this->returnValue($reservation));

		$this->service->GetReservation($referenceNumber);

		$expectedResponse = RestResponse::NotFound();
		$this->assertEquals($expectedResponse, $this->server->_LastResponse);
	}
}