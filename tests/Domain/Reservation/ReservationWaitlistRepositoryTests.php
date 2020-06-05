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

class ReservationWaitlistRepositoryTests extends TestBase
{
    /**
     * @var ReservationWaitlistRepository
     */
    public $repository;

    public function setUp(): void
    {
        parent::setup();

        $this->repository = new ReservationWaitlistRepository();
    }

    public function testAddsRequest()
    {
        $startDate = Date::Now();
        $endDate = Date::Now();
        $userId = 1;
        $resourceIds = array(1, 2, 3);
        $request = ReservationWaitlistRequest::Create($userId, $startDate, $endDate, $resourceIds);

        $id = $this->repository->Add($request);

        $this->assertEquals($this->db->_ExpectedInsertId, $id);
        $this->assertEquals(new AddReservationWaitlistCommand($userId, $startDate, $endDate, $resourceIds), $this->db->_LastCommand);

    }
}