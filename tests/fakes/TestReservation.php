<?php

/**
 * Copyright 2011-2020 Nick Korbel
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

class TestReservation extends Reservation
{
	/**
	 * @param string $referenceNumber
	 * @param DateRange $reservationDate
	 * @param int $reservationId
	 */
	public function __construct($referenceNumber = null, $reservationDate = null, $reservationId = null)
	{
		$this->startDate = new NullDate();
		$this->endDate = new NullDate();
		$this->checkinDate = new NullDate();
		$this->checkoutDate = new NullDate();
		$this->previousStart = new NullDate();
		$this->previousEnd = new NullDate();

		if (!empty($referenceNumber))
		{
			$this->SetReferenceNumber($referenceNumber);
		}
		else
		{
			$this->SetReferenceNumber(uniqid('', true));
		}

		if ($reservationDate != null)
		{
			$this->SetReservationDate($reservationDate);
		}
		else
		{
			$this->SetReservationDate(new TestDateRange());
		}

		if ($reservationId == null)
        {
            $reservationId = uniqid();
        }
		$this->SetReservationId($reservationId);
	}

	public function WithAddedInvitees($inviteeIds)
	{
		$this->addedInvitees = $inviteeIds;
	}

	public function WithAddedParticipants($participantIds)
	{
		$this->addedParticipants = $participantIds;
	}

	public function WithExistingParticipants($participantIds)
	{
		$this->unchangedParticipants = $participantIds;
	}
}