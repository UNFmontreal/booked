<?php
/**
 * Copyright 2017-2020 Nick Korbel
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

require_once(ROOT_DIR . 'Presenters/Reservation/ReservationWaitlistPresenter.php');

class ReservationWaitlistPresenterTests extends TestBase
{
    /**
     * @var FakeReservationWaitlistPage
     */
    public $page;

    /**
     * @var FakeReservationWaitlistRepository
     */
    public $repository;

    /**
     * @var ReservationWaitlistPresenter
     */
    private $presenter;

    public function setUp(): void
    {
        parent::setup();
        $this->page = new FakeReservationWaitlistPage();
        $this->repository = new FakeReservationWaitlistRepository();
        $this->presenter = new ReservationWaitlistPresenter($this->page, $this->fakeUser, $this->repository);

    }

    public function testAddsWaitlistRequest()
    {
        $startDate = Date::Parse($this->page->_StartDate . ' ' . $this->page->_StartTime, $this->fakeUser->Timezone);
        $endDate = Date::Parse($this->page->_EndDate . ' ' . $this->page->_EndTime, $this->fakeUser->Timezone);
        $resourceId = $this->page->_ResourceId;

        $expectedWaitlistRequest = ReservationWaitlistRequest::Create($this->page->_UserId, $startDate, $endDate, $resourceId);

        $this->presenter->PageLoad();

        $this->assertEquals($expectedWaitlistRequest, $this->repository->_AddedWaitlistRequest);
    }
}

class FakeReservationWaitlistPage implements IReservationWaitlistPage
{
    /**
     * @var int
     */
    public $_UserId;

    /**
     * @var string
     */
    public $_StartDate;

    /**
     * @var string
     */
    public $_EndDate;

    /**
     * @var string
     */
    public $_StartTime;

    /**
     * @var string
     */
    public $_EndTime;

    /**
     * @var int
     */
    public $_ResourceId;

    /**
     * FakeReservationWaitlistPage constructor.
     */
    public function __construct()
    {
        $this->_UserId = 123;
        $this->_StartDate = '2016-06-25';
        $this->_StartTime = '14:30';
        $this->_EndDate = '2016-06-25';
        $this->_EndTime = '16:45';
        $this->_ResourceId = 999;
    }

    /**
     * @param bool $succeeded
     */
    public function SetSaveSuccessfulMessage($succeeded)
    {
        // TODO: Implement SetSaveSuccessfulMessage() method.
    }

    /**
     * @param array|string[] $errors
     */
    public function SetErrors($errors)
    {
        // TODO: Implement SetErrors() method.
    }

    /**
     * @param array|string[] $warnings
     */
    public function SetWarnings($warnings)
    {
        // TODO: Implement SetWarnings() method.
    }

    /**
     * @param array|string[] $messages
     */
    public function SetRetryMessages($messages)
    {
        // TODO: Implement SetRetryMessages() method.
    }

    /**
     * @param bool $canBeRetried
     */
    public function SetCanBeRetried($canBeRetried)
    {
        // TODO: Implement SetCanBeRetried() method.
    }

    /**
     * @param ReservationRetryParameter[] $retryParameters
     */
    public function SetRetryParameters($retryParameters)
    {
        // TODO: Implement SetRetryParameters() method.
    }

    /**
     * @return ReservationRetryParameter[]
     */
    public function GetRetryParameters()
    {
        // TODO: Implement GetRetryParameters() method.
    }

    /**
     * @param bool $canJoinWaitlist
     */
    public function SetCanJoinWaitList($canJoinWaitlist)
    {
        // TODO: Implement SetCanJoinWaitList() method.
    }

    /**
     * @return string
     */
    public function GetReferenceNumber()
    {
        // TODO: Implement GetReferenceNumber() method.
    }

    /**
     * @return string
     */
    public function GetAction()
    {
        // TODO: Implement GetAction() method.
    }

    /**
     * @return int
     */
    public function GetUserId()
    {
        return $this->_UserId;
    }

    /**
     * @return string
     */
    public function GetStartDate()
    {
        return $this->_StartDate;
    }

    /**
     * @return string
     */
    public function GetEndDate()
    {
        return $this->_EndDate;
    }

    /**
     * @return string
     */
    public function GetStartTime()
    {
        return $this->_StartTime;
    }

    /**
     * @return string
     */
    public function GetEndTime()
    {
        return $this->_EndTime;
    }

    /**
     * @return int
     */
    public function GetResourceId()
    {
        return $this->_ResourceId;
    }
}