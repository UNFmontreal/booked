<?php
/**
Copyright 2011-2020 Nick Korbel

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

require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

class ResourceMinimumDurationRuleTests extends TestBase
{
	public function setUp(): void
	{
		parent::setup();
	}

	public function teardown(): void
	{
		parent::teardown();
	}

	public function testNotValidIfTheReservationIsShorterThanTheMinDurationForAnyResource()
	{
		$resource1 = new FakeBookableResource(1, "1");
		$resource1->SetMinLength(null);

		$resource2 = new FakeBookableResource(2, "2");
		$resource2->SetMinLength("25h00m");

		$reservation = new TestReservationSeries();

		$duration = new DateRange(Date::Now(), Date::Now()->AddDays(1));
		$reservation->WithDuration($duration);
		$reservation->WithResource($resource1);
		$reservation->AddResource($resource2);

		$rule = new ResourceMinimumDurationRule();
		$result = $rule->Validate($reservation, null);

		$this->assertFalse($result->IsValid());
	}

	public function testOkIfReservationIsLongerThanTheMinDuration()
	{
		$resource = new FakeBookableResource(1, "2");
		$resource->SetMinLength("23h00m");

		$reservation = new TestReservationSeries();
		$reservation->WithResource($resource);

		$duration = new DateRange(Date::Now(), Date::Now()->AddDays(1));
		$reservation->WithDuration($duration);

		$rule = new ResourceMinimumDurationRule();
		$result = $rule->Validate($reservation, null);

		$this->assertTrue($result->IsValid());
	}
}
?>