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

require_once(ROOT_DIR . 'Presenters/Reservation/ReservationUserAvailabilityPresenter.php');

interface IReservationUserAvailabilityPage
{

    /**
     * @return int[]
     */
    public function GetResourceIds();

    /**
     * @return int[]
     */
    public function GetInviteeIds();

    /**
     * @return int[]
     */
    public function GetParticipantIds();

    /**
     * @return int
     */
    public function GetScheduleId();

    /**
     * @param DailyLayout $dailyLayout
     * @param BookableResource[] $resources
     * @param UserDto $user
     * @param UserDto[] $participants
     * @param UserDto[] $invitees
     * @param DateRange $dateRange
     */
    public function Bind($dailyLayout, $resources, $user, $participants, $invitees, $dateRange);

    /**
     * @return string
     */
    public function GetStartDate();

    /**
     * @return string
     */
    public function GetStartTime();
    /**
     * @return string
     */
    public function GetEndDate();
    /**
     * @return string
     */
    public function GetEndTime();
}

class ReservationUserAvailabilityPage extends Page implements IReservationUserAvailabilityPage
{
    /**
     * @var ReservationUserAvailabilityPresenter
     */
    private $presenter;

    public function __construct()
    {
        parent::__construct('', 1);
        $this->presenter = new ReservationUserAvailabilityPresenter(
            $this,
            new ReservationViewRepository(),
            new ScheduleRepository(),
            new UserRepository(),
            new ResourceRepository());
    }

    public function PageLoad()
    {
        if (Configuration::Instance()->GetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_HIDE_USER_DETAILS, new BooleanConverter())) {
            return;
        }

        $this->Set('DisplaySlotFactory', new DisplaySlotFactory());
        $this->presenter->PageLoad($this->server->GetUserSession());
    }

    public function GetResourceIds()
    {
        return $this->GetQuerystring(QueryStringKeys::RESOURCE_ID, true);
    }

    public function GetInviteeIds()
    {
        return $this->GetQuerystring(QueryStringKeys::INVITEE_ID, true);
    }

    public function GetParticipantIds()
    {
        return $this->GetQuerystring(QueryStringKeys::PARTICIPANT_ID, true);
    }

    public function GetScheduleId()
    {
        return $this->GetQuerystring(QueryStringKeys::SCHEDULE_ID);
    }

    public function Bind($dailyLayout, $resources, $user, $participants, $invitees, $dateRange)
    {
        $this->Set('DailyLayout', $dailyLayout);
        $this->Set('BoundDates', $dateRange->Dates());
        $this->Set('Resources', $resources);
        $this->Set('User', $user);
        $this->Set('Participants', $participants);
        $this->Set('Invitees', $invitees);
        $this->Display('Reservation/availability.tpl');
    }

    public function GetStartDate()
    {
       return $this->GetQuerystring(QueryStringKeys::START_DATE);
    }

    public function GetStartTime()
    {
        return $this->GetQuerystring(QueryStringKeys::START_TIME);
    }

    public function GetEndDate()
    {
        return $this->GetQuerystring(QueryStringKeys::END_DATE);
    }

    public function GetEndTime()
    {
        return $this->GetQuerystring(QueryStringKeys::END_TIME);
    }
}
