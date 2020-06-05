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

require_once(ROOT_DIR . 'Pages/ActionPage.php');
require_once(ROOT_DIR . 'Pages/SecurePage.php');
require_once(ROOT_DIR . 'Pages/Ajax/AutoCompletePage.php');
require_once(ROOT_DIR . 'Presenters/Search/SearchReservationsPresenter.php');
require_once(ROOT_DIR . 'lib/Application/Schedule/namespace.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');

interface ISearchReservationsPage extends IActionPage
{
	/**
	 * @param ResourceDto[] $resources
	 */
	public function SetResources($resources);

	/**
	 * @param Schedule[] $schedules
	 */
	public function SetSchedules($schedules);

	public function SetCurrentUser(UserSession $userSession);

	/**
	 * @param ReservationItemView[] $reservations
	 * @param string $timezone
	 */
	public function ShowReservations($reservations, $timezone);

	/**
	 * @return string
	 */
	public function GetRequestedRange();

	/**
	 * @return string
	 */
	public function GetRequestedStartDate();

	/**
	 * @return string
	 */
	public function GetRequestedEndDate();

	/**
	 * @return int[]
	 */
	public function GetResources();

	/**
	 * @return int[]
	 */
	public function GetSchedules();

	/**
	 * @return int
	 */
	public function GetUserId();

	/**
	 * @return string
	 */
	public function GetTitle();

	/**
	 * @return string
	 */
	public function GetDescription();

	/**
	 * @return string
	 */
	public function GetReferenceNumber();

	/**
	 * @param Date $today
	 */
	public function SetToday($today);
}

class SearchReservationsPage extends ActionPage implements ISearchReservationsPage
{
	/**
	 * @var SearchReservationsPresenter
	 */
	private $presenter;

	public function __construct()
	{
		parent::__construct('SearchReservations');

		$resourceService = ResourceService::Create();
		$this->presenter = new SearchReservationsPresenter($this,
														   ServiceLocator::GetServer()->GetUserSession(),
														   new ReservationViewRepository(),
														   $resourceService,
														   new ScheduleService(new ScheduleRepository(), $resourceService, new DailyLayoutFactory()));
	}

	public function ProcessAction()
	{
		$this->presenter->ProcessAction();
	}

	public function ProcessDataRequest($dataRequest)
	{
		// no-op
	}

	public function ProcessPageLoad()
	{
		$this->presenter->PageLoad();
		$this->Display('Search/search-reservations.tpl');
	}

	public function SetResources($resources)
	{
		$this->Set('Resources', $resources);
	}

	public function SetSchedules($schedules)
	{
		$this->Set('Schedules', $schedules);
	}

	public function SetCurrentUser(UserSession $userSession)
	{
		$this->Set('UserNameFilter', sprintf('%s (%s)', new FullName($userSession->FirstName, $userSession->LastName), $userSession->Email));
		$this->Set('UserIdFilter', $userSession->UserId);
	}

	public function SetToday($today)
	{
		$this->Set('Today', $today);
		$this->Set('Tomorrow', $today->AddDays(1));
	}

	public function ShowReservations($reservations, $timezone)
	{
		$this->Set('Reservations', $reservations);
		$this->Set('Timezone', $timezone);
		$this->Display('Search/search-reservations-results.tpl');
	}

	public function GetRequestedRange()
	{
		return $this->GetForm(FormKeys::AVAILABILITY_RANGE);
	}

	public function GetRequestedStartDate()
	{
		return $this->GetForm(FormKeys::BEGIN_DATE);
	}

	public function GetRequestedEndDate()
	{
		return $this->GetForm(FormKeys::END_DATE);
	}

	public function GetUserId()
	{
		return $this->GetForm(FormKeys::USER_ID);
	}

	public function GetResources()
	{
		$resources = $this->GetForm(FormKeys::RESOURCE_ID);
		if (empty($resources))
		{
			return array();
		}

		return $resources;
	}

	public function GetSchedules()
	{
		$schedules = $this->GetForm(FormKeys::SCHEDULE_ID);
		if (empty($schedules))
		{
			return array();
		}

		return $schedules;
	}

	public function GetTitle()
	{
		return $this->GetForm(FormKeys::RESERVATION_TITLE);
	}

	public function GetDescription()
	{
		return $this->GetForm(FormKeys::DESCRIPTION);
	}

	public function GetReferenceNumber()
	{
		return $this->GetForm(FormKeys::REFERENCE_NUMBER);
	}

}

