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

require_once(ROOT_DIR . 'Presenters/Reservation/GuestReservationPresenter.php');

class GuestReservationPresenterTests extends TestBase
{
    /**
     * @var FakeGuestReservationPage
     */
    private $page;
    /**
     * @var GuestReservationPresenter
     */
    private $presenter;

    /**
     * @var IReservationInitializerFactory
     */
    private $factory;

    /**
     * @var INewReservationPreconditionService
     */
    private $preconditionService;
    /**
     * @var FakeRegistration
     */
    private $registration;
    /**
     * @var IReservationInitializer
     */
    private $initializer;
    /**
     * @var FakeWebAuthentication
     */
    private $authentication;

    public function setUp(): void
    {
        $this->page = new FakeGuestReservationPage();
        $this->registration = new FakeRegistration();
        $this->factory = $this->createMock('IReservationInitializerFactory');
        $this->preconditionService = $this->createMock('INewReservationPreconditionService');
        $this->initializer = $this->createMock('IReservationInitializer');
        $this->authentication = new FakeWebAuthentication();

        $this->factory->expects($this->any())
            ->method('GetNewInitializer')
            ->with($this->anything())
            ->will($this->returnValue($this->initializer));

        $this->presenter = new GuestReservationPresenter($this->page,
            $this->registration,
            $this->authentication,
            $this->factory,
            $this->preconditionService);
        parent::setup();
    }

    public function testRegistersAGuestAccount()
    {
        $this->page->_GuestInformationCollected = false;
        $this->page->_Email = 'email@address.com';
        $this->page->_CreatingAccount = true;

        $this->initializer->expects($this->once())
            ->method('Initialize');

        $this->presenter->PageLoad();

        $this->assertEquals($this->page->_Email, $this->registration->_Email);
        $this->assertTrue($this->registration->_RegisterCalled);
        $this->assertEquals($this->authentication->_LastLogin, $this->page->_Email);
    }

    public function testPermissionStrategyAddsPermissionForAllScheduleResources()
    {
        $this->page->_ScheduleId = 455;
        $strategy = new GuestReservationPermissionStrategy($this->page);

        $user = new FakeUser(123);

        $strategy->AddAccount($user);

        $this->assertTrue($this->db->ContainsCommand(new AutoAssignGuestPermissionsCommand($user->Id(), $this->page->_ScheduleId)));
    }
}

class FakeGuestReservationPage implements IGuestReservationPage
{
    public $_GuestInformationCollected = false;
    public $_Email;
    public $_CreatingAccount = false;
    public $_ScheduleId;

    public function GuestInformationCollected()
    {
        return $this->_GuestInformationCollected;
    }

    public function GetEmail()
    {
        return $this->_Email;
    }

    public function IsCreatingAccount()
    {
        return $this->_CreatingAccount;
    }

    public function PageLoad()
    {
    }

    public function Redirect($url)
    {
    }

    public function RedirectToError($errorMessageId = ErrorMessages::UNKNOWN_ERROR, $lastPage = '')
    {
    }

    public function IsPostBack()
    {
        return true;
    }

    public function IsValid()
    {
        return true;
    }

    public function GetLastPage($defaultPage = '')
    {
    }

    public function RegisterValidator($validatorId, $validator)
    {
    }

    public function EnforceCSRFCheck()
    {
    }

    public function GetRequestedResourceId()
    {
    }

    public function GetRequestedScheduleId()
    {
        return $this->_ScheduleId;
    }

    public function GetReservationDate()
    {
    }

    public function GetStartDate()
    {
    }

    public function GetEndDate()
    {
    }

    public function BindPeriods($startPeriods, $endPeriods, $lockPeriods)
    {
    }

    public function BindAvailableResources($resources)
    {
    }

    public function BindAvailableAccessories($accessories)
    {
    }

    public function BindResourceGroups($groups)
    {
    }

    public function SetSelectedStart(SchedulePeriod $selectedStart, Date $startDate)
    {
    }

    public function SetSelectedEnd(SchedulePeriod $selectedEnd, Date $endDate)
    {
    }

    public function SetRepeatTerminationDate($repeatTerminationDate)
    {
    }

    public function SetReservationUser(UserDto $user)
    {
    }

    public function SetReservationResource($resource)
    {
    }

    public function SetScheduleId($scheduleId)
    {
    }

    public function SetParticipants($participants)
    {
    }

    public function SetInvitees($invitees)
    {
    }

    public function SetAccessories($accessories)
    {
    }

    public function SetAttachments($attachments)
    {
    }

    public function SetCanChangeUser($canChangeUser)
    {
    }

    public function ShowAdditionalResources($canShowAdditionalResources)
    {
    }

    public function ShowUserDetails($canShowUserDetails)
    {
    }

    public function SetShowParticipation($shouldShow)
    {
    }

    public function ShowReservationDetails($showReservationDetails)
    {
    }

    public function HideRecurrence($isHidden)
    {
    }

    function SetAllowParticipantsToJoin($allowParticipation)
    {
    }

    public function GetSortField()
    {
    }

    public function GetSortDirection()
    {
    }

    public function SetStartReminder($reminderValue, $reminderInterval)
    {
    }

    public function SetEndReminder($reminderValue, $reminderInterval)
    {
    }

    public function SetTerms($termsOfService)
    {
    }

    public function SetAvailability(DateRange $availability)
    {
    }

    public function SetFirstWeekday($weekday)
    {
    }

    public function MakeUnavailable()
    {
        // TODO: Implement MakeUnavailable() method.
    }

    public function IsUnavailable()
    {
        // TODO: Implement IsUnavailable() method.
    }

    public function SetTermsAccepted($accepted)
    {
        // TODO: Implement SetTermsAccepted() method.
    }

    public function GetTermsOfServiceAcknowledgement()
    {
        return true;
    }

	public function SetMaximumResources($maximum)
	{
		// TODO: Implement SetMaximumResources() method.
	}
}
