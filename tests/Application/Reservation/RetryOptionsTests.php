<?php
/**
 * Copyright 2020 Nick Korbel
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

require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

class RetryOptionsTests extends TestBase
{
	/**
	 * @var FakeReservationConflictIdentifier
	 */
	private $conflictIdentifier;
	/**
	 * @var FakeScheduleRepository
	 */
	private $scheduleRepository;

	public function setUp(): void
	{
		parent::setUp();

		$this->conflictIdentifier = new FakeReservationConflictIdentifier();
		$this->scheduleRepository = new FakeScheduleRepository();

		$this->fakeConfig->SetSectionKey(ConfigSection::CREDITS, ConfigKeys::CREDITS_ENABLED, "true");
	}

	public function testRemovesConflictsFromReservation()
	{
		$now = Date::Now();
		$layout = new FakeScheduleLayout();
		$layout->_SlotCount = new SlotCount(1, 0);

		$this->scheduleRepository->_Layout = $layout;

		$requiredCredits = 3;
		$resource = new FakeBookableResource(1);
		$resource->SetCreditsPerSlot(1);
		$resource->SetPeakCreditsPerSlot(1);

		$current = new TestReservation("1", new DateRange($now->AddDays(1), $now->AddDays(1)->AddHours(1)));
		$conflict1 = new TestReservation("2", new DateRange($now->AddDays(2), $now->AddDays(2)->AddHours(1)));
		$conflict2 = new TestReservation("3", new DateRange($now->AddDays(3), $now->AddDays(3)->AddHours(1)));
		$nonConflict1 = new TestReservation("4", new DateRange($now->AddDays(4), $now->AddDays(4)->AddHours(1)));
		$nonConflict2 = new TestReservation("5", new DateRange($now->AddDays(5), $now->AddDays(5)->AddHours(1)));
		$series = (new ExistingReservationSeriesBuilder())
				->WithPrimaryResource($resource)
				->WithBookedBy($this->fakeUser)
				->WithCurrentInstance($current)
				->WithInstance($conflict1)
				->WithInstance($conflict2)
				->WithInstance($nonConflict1)
				->WithInstance($nonConflict2)
				->Build();

		$series->CalculateCredits($layout);


		$this->conflictIdentifier->_Conflicts = [
				new IdentifiedConflict($conflict1, new TestReservationItemView("100", $conflict1->StartDate(), $conflict1->EndDate(), 1, "2")),
				new IdentifiedConflict($conflict2, new TestReservationItemView("200", $conflict2->StartDate(), $conflict2->EndDate(), 1, "3"))
		];

		$retryOptions = new ReservationRetryOptions($this->conflictIdentifier, $this->scheduleRepository);
		$retryOptions->AdjustReservation($series, [new ReservationRetryParameter(ReservationRetryParameter::$SKIP_CONFLICTS, "true")]);

		$instances = $series->Instances();
		$this->assertEquals(3, count($instances));
		$this->assertEquals($requiredCredits, $series->GetCreditsRequired());
		$this->assertEquals([], array_filter($instances, function(Reservation $i){ return $i->ReferenceNumber() == "2";}));
		$this->assertEquals([], array_filter($instances, function(Reservation $i){ return $i->ReferenceNumber() == "3";}));
		$this->assertEquals($current, $instances[0]);
		$this->assertEquals($nonConflict1, $instances[1]);
		$this->assertEquals($nonConflict2, $instances[2]);
	}
}

class FakeReservationConflictIdentifier implements IReservationConflictIdentifier
{

	/**
	 * @var IdentifiedConflict[]
	 */
	public $_Conflicts = [];

	/**
	 * @inheritDoc
	 */
	public function GetConflicts($reservationSeries)
	{
		return $this->_Conflicts;
	}
}
