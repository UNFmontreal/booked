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

class TestReservationItemView extends ReservationItemView
{
    public $_RequiresCheckin = false;

	/**
	 * @param null|string $id
	 * @param Date $startDate
	 * @param Date $endDate
	 * @param int $resourceId
	 * @param string|null $referenceNumber
	 */
	public function __construct($id, Date $startDate, Date $endDate, $resourceId = 1, $referenceNumber = null)
	{
		parent::__construct();

		$this->ReservationId = $id;
		$this->StartDate = $startDate;
		$this->EndDate = $endDate;
		$this->ResourceId = $resourceId;
		$this->Date = new DateRange($startDate, $endDate);
		$this->RepeatType = RepeatType::None;
		$this->ReferenceNumber = $referenceNumber;
		$this->ScheduleId = 1;
	}

	public function WithSeriesId($seriesId)
	{
		$this->SeriesId = $seriesId;
		return $this;
	}

    public function RequiresCheckin()
    {
        return $this->_RequiresCheckin;
    }
}

class TestBlackoutItemView extends BlackoutItemView
{
	public function __construct(
		$instanceId,
		Date $startDate,
		Date $endDate,
		$resourceId,
		$seriesId = 1)
	{
		parent::__construct($instanceId, $startDate, $endDate, $resourceId, null, null, null, null, null, null, null, $seriesId, null, null);
	}

	public function WithScheduleId($scheduleId)
	{
		$this->ScheduleId = $scheduleId;
		return $this;
	}

}