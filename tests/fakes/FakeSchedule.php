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

require_once(ROOT_DIR . 'Domain/Schedule.php');

class FakeSchedule extends Schedule
{
    public function __construct($id = 1, $name = 'test', $isDefault = true, $weekdayStart = 0, $daysVisible = 7)
    {
        parent::__construct($id, $name, $isDefault, $weekdayStart, $daysVisible);
        $this->_timezone = 'America/Chicago';
        $this->SetAvailability(new Date('2018-01-01', $this->_timezone), new Date('2018-02-02', $this->_timezone));
    }
}