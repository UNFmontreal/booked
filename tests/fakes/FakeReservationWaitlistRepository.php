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

require_once (ROOT_DIR . 'Domain/Access/namespace.php');

class FakeReservationWaitlistRepository implements IReservationWaitlistRepository
{

    /**
     * @var ReservationWaitlistRequest
     */
    public $_AddedWaitlistRequest;

    public $_LastAddedId = 120;

    /**
     * @var ReservationWaitlistRequest[]
     */
    public $_AllRequests = array();

    /**
     * @param ReservationWaitlistRequest $request
     * @return int
     */
    public function Add(ReservationWaitlistRequest $request)
    {
        $this->_AddedWaitlistRequest = $request;
        return $this->_LastAddedId;
    }

    /**
     * @return ReservationWaitlistRequest[]
     */
    public function GetAll()
    {
       return $this->_AllRequests;
    }

    /**
     * @param int $waitlistId
     * @return ReservationWaitlistRequest
     */
    public function LoadById($waitlistId)
    {
        // TODO: Implement LoadById() method.
    }

    /**
     * @param ReservationWaitlistRequest $request
     */
    public function Delete(ReservationWaitlistRequest $request)
    {
        // TODO: Implement Delete() method.
    }
}