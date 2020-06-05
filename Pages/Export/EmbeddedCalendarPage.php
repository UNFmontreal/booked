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

require_once(ROOT_DIR . 'Pages/Page.php');
require_once(ROOT_DIR . 'Presenters/EmbeddedCalendarPresenter.php');

interface IEmbeddedCalendarPage
{
    /**
     * @param ReservationListing $reservations
     * @param string $timezone
     * @param Date $startDate
     * @param Date $endDate
     */
    public function BindReservations($reservations, $timezone, Date $startDate, Date $endDate);

    public function DisplayAgenda();

    public function DisplayWeek();

    public function DisplayMonth();

    public function DisplayError();

    /**
     * @return string
     */
    public function GetScheduleId();

    /**
     * @return string
     */
    public function GetResourceId();

    /**
     * @return int
     */
    public function GetDaysToShow();

    /**
     * @return string
     */
    public function GetDisplayType();

    /**
     * @return string
     */
    public function GetTitleFormat();

    /**
     * @param EmbeddedCalendarTitleFormatter $formatter
     */
    public function BindTitleFormatter(EmbeddedCalendarTitleFormatter $formatter);

}

class EmbeddedCalendarPage extends Page implements IEmbeddedCalendarPage
{
    private $presenter;

    public function __construct()
    {
        $this->presenter = new EmbeddedCalendarPresenter($this, new ReservationViewRepository(), new ResourceRepository(), new ScheduleRepository());
        parent::__construct('', 1);
    }

    public function PageLoad()
    {
        $this->Set('ReservationUrl', Configuration::Instance()->GetScriptUrl() . '/' . Pages::RESERVATION . '?' . QueryStringKeys::REFERENCE_NUMBER . '=');
        $this->Set('ScheduleUrl', Configuration::Instance()->GetScriptUrl() . '/' . Pages::SCHEDULE . '?' . QueryStringKeys::START_DATE . '=');
        $this->presenter->PageLoad();
    }

    public function BindReservations($reservations, $timezone, Date $startDate, Date $endDate)
    {
        $Range = new DateRange($startDate, $endDate);
        $this->Set('Reservations', $reservations);
        $this->Set('Timezone', $timezone);
        $this->Set('Range', $Range);
        $this->Set('Width', (1/7)*100 . '%');
    }

    public function DisplayAgenda()
    {
        $this->Display('Export/embedded-calendar-agenda.tpl');
    }

    public function DisplayWeek()
    {
        $this->Display('Export/embedded-calendar-week.tpl');
    }

    public function DisplayMonth()
    {
        $this->Display('Export/embedded-calendar-month.tpl');
    }

    public function DisplayError()
    {
        echo 'We are having trouble loading reservations right now';
    }

    public function GetScheduleId()
    {
        return $this->GetQuerystring(QueryStringKeys::SCHEDULE_ID);
    }

    public function GetResourceId()
    {
        return $this->GetQuerystring(QueryStringKeys::RESOURCE_ID);
    }

    public function GetDaysToShow()
    {
        return $this->GetQuerystring(QueryStringKeys::DAY);
    }

    public function GetDisplayType()
    {
        return $this->GetQuerystring(QueryStringKeys::TYPE);
    }

    public function GetTitleFormat()
    {
        return $this->GetQuerystring(QueryStringKeys::FORMAT);
    }

    public function BindTitleFormatter(EmbeddedCalendarTitleFormatter $formatter)
    {
       $this->Set('TitleFormatter', $formatter);
    }


}