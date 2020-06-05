<?php
/**
 * Copyright 2014-2020 Nick Korbel
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

require_once(ROOT_DIR . 'Presenters/Reservation/ReservationAttributesPresenter.php');

class ReservationAttributesPresenterTests extends TestBase
{
	/**
	 * @var IAttributeService|PHPUnit_Framework_MockObject_MockObject
	 */
	private $attributeService;

	/**
	 * @var FakeReservationAuthorization
	 */
	private $authorizationService;

	/**
	 * @var FakeReservationAttributesPage
	 */
	private $page;

	/**
	 * @var FakePrivacyFilter
	 */
	private $privacyFilter;

	/**
	 * @var FakeReservationViewRepository
	 */
	private $reservationRepository;

	/**
	 * @var ReservationAttributesPresenter
	 */
	private $presenter;

	/**
	 * @var IAttributeRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $attributeRepository;

	public function setUp(): void
	{
		parent::setup();

		$this->fakeConfig->SetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_VIEW_RESERVATIONS, true);
		$this->page = new FakeReservationAttributesPage();

		$this->attributeRepository = $this->createMock('IAttributeRepository');
		$this->attributeService = new AttributeService($this->attributeRepository);
		$this->authorizationService = new FakeAuthorizationService();
		$this->attributeService->SetAuthorizationService($this->authorizationService);
		$this->privacyFilter = new FakePrivacyFilter();
		$this->reservationRepository = new FakeReservationViewRepository();

		$this->presenter = new ReservationAttributesPresenter($this->page,
															  $this->attributeService,
															  $this->authorizationService,
															  $this->privacyFilter,
															  $this->reservationRepository
		);

		$this->privacyFilter->_CanViewDetails = true;
	}

	public function testForSecondaryUserAttributes_ShowIfTheCurrentUserCanBookForRequestedUser()
	{
		$requestedUserId = 9292;

		$attributeWithoutSecondaryEntity = new FakeCustomAttribute(1);
		$attributeWithSecondaryEntityOfRequestedUser = new FakeCustomAttribute(2);
		$attributeWithSecondaryEntityOfRequestedUser->WithSecondaryEntities(CustomAttributeCategory::USER, $requestedUserId);

		$attributeWithSecondaryEntityOfAnotherUser = new FakeCustomAttribute(3);
		$attributeWithSecondaryEntityOfAnotherUser->WithSecondaryEntities(CustomAttributeCategory::USER, 1212);

		$attributes = array(
				$attributeWithoutSecondaryEntity,
				$attributeWithSecondaryEntityOfRequestedUser,
				$attributeWithSecondaryEntityOfAnotherUser,
		);

		$this->authorizationService->_CanReserveFor = true;

		$this->attributeRepository->expects($this->once())
							   ->method('GetByCategory')
							   ->with($this->equalTo(CustomAttributeCategory::RESERVATION))
							   ->will($this->returnValue($attributes));

		$this->page->_RequestedUserId = $requestedUserId;

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(2, $this->page->_Attributes);
		$this->assertEquals($attributeWithoutSecondaryEntity->Id(), $this->page->_Attributes[0]->Id());
		$this->assertEquals($attributeWithSecondaryEntityOfRequestedUser->Id(), $this->page->_Attributes[1]->Id());
	}

	public function testForSecondaryUserAttributes_DoNotShowIfTheCurrentUserCanBookForRequestedUser()
	{
		$requestedUserId = 9292;

		$attributeWithoutSecondaryEntity = new FakeCustomAttribute(1);
		$attributeWithSecondaryEntityOfRequestedUser = new FakeCustomAttribute(2);
		$attributeWithSecondaryEntityOfRequestedUser->WithSecondaryEntities(CustomAttributeCategory::USER, $requestedUserId);

		$attributes = array(
				$attributeWithoutSecondaryEntity,
				$attributeWithSecondaryEntityOfRequestedUser
		);

		$this->authorizationService->_CanReserveFor = false;

		$this->attributeRepository->expects($this->once())
							   ->method('GetByCategory')
							   ->with($this->equalTo(CustomAttributeCategory::RESERVATION))
							   ->will($this->returnValue($attributes));

		$this->page->_RequestedUserId = $requestedUserId;

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(1, $this->page->_Attributes);
		$this->assertEquals($attributeWithoutSecondaryEntity->Id(), $this->page->_Attributes[0]->Id());
	}

	public function testWhenExistingReservationIsRequested_ThenLoadValuesForAttributes()
	{
		$requestedRefNum = '8882';

		$attributes = array(
				new FakeCustomAttribute(1),
				new FakeCustomAttribute(2),
		);

		$this->attributeRepository->expects($this->once())
									   ->method('GetByCategory')
									   ->with($this->equalTo(CustomAttributeCategory::RESERVATION))
									   ->will($this->returnValue($attributes));

		$this->reservationRepository->_ReservationView->AddAttribute(new AttributeValue(1, 'value1'));
		$this->reservationRepository->_ReservationView->AddAttribute(new AttributeValue(2, 'value2'));

		$this->page->_RequestedReferenceNumber = $requestedRefNum;

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(2, $this->page->_Attributes);
		$this->assertEquals(1, $this->page->_Attributes[0]->Id());
		$this->assertEquals(2, $this->page->_Attributes[1]->Id());

		$this->assertEquals('value1', $this->page->_Attributes[0]->Value());
		$this->assertEquals('value2', $this->page->_Attributes[1]->Value());
	}

	public function testIfCannotViewDetails_ThenDoNotAddAnyAttributes()
	{
		$this->page->_RequestedReferenceNumber = 'something';

		$this->privacyFilter->_CanViewDetails = false;

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(0, $this->page->_Attributes);
	}

	public function testIfTheAttributeIsPrivate_AndTheCurrentUserIsNotTheOwnerOrAdmin_ThenDoNotAddTheAttributes()
	{
		$this->page->_RequestedReferenceNumber = 'something';

		$privateAttribute = new FakeCustomAttribute(1);
		$privateAttribute->WithIsPrivate(true);

		$attributes = array(
				$privateAttribute,
						new FakeCustomAttribute(2),
		);

		$this->attributeRepository->expects($this->once())
									   ->method('GetByCategory')
									   ->with($this->equalTo(CustomAttributeCategory::RESERVATION))
									   ->will($this->returnValue($attributes));

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(1, $this->page->_Attributes);
	}

	public function testIfTheAttributeIsPrivate_AndTheCurrentUserCanReserveForRequestedUser_ThenAddTheAttributes()
	{
		$this->page->_RequestedReferenceNumber = 'something';

		$this->authorizationService->_CanReserveFor = true;

		$privateAttribute = new FakeCustomAttribute(1);
		$privateAttribute->WithIsPrivate(true);

		$attributes = array(
				$privateAttribute,
						new FakeCustomAttribute(2),
		);

		$this->attributeRepository->expects($this->once())
									   ->method('GetByCategory')
									   ->with($this->equalTo(CustomAttributeCategory::RESERVATION))
									   ->will($this->returnValue($attributes));

		$this->presenter->PageLoad($this->fakeUser);

		$this->assertCount(2, $this->page->_Attributes);
	}
}

class FakeReservationAttributesPage implements IReservationAttributesPage
{
	/**
	 * @var Attribute[]
	 */
	public $_Attributes;

	/**
	 * @var int
	 */
	public $_RequestedUserId;

	/**
	 * @var int
	 */
	public $_RequestedReferenceNumber;

	/**
	 * @return int
	 */
	public function GetRequestedUserId()
	{
		return $this->_RequestedUserId;
	}

	/**
	 * @param Attribute[] $attributes
	 */
	public function SetAttributes($attributes)
	{
		$this->_Attributes = $attributes;
	}

	/**
	 * @return int
	 */
	public function GetRequestedReferenceNumber()
	{
		return $this->_RequestedReferenceNumber;
	}

	/**
	 * @return int[]
	 */
	public function GetRequestedResourceIds()
	{
		// TODO: Implement GetRequestedResourceIds() method.
	}
}