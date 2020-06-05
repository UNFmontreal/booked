{*
Eredeti angol jogi nyilatkozat:

Copyright 2013-2020 Nick Korbel

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

---
Az Eredeti angol jogi nyilatkozat magyar fordítása:

Szerzői jog tulajdonos: 2011-2020 Nick Korbel

Ez a fájl a Booked Scheduler része.

Booked Scheduler szabad szoftver: terjesztheted vagy módosíthatod a GNU Általános Nyilvános Licensz 
bármely 3 változata vagy (belátásod alapjánszerint) későbbi változatok alapján,
amelyeket a Free Software Foundation, adott ki. 

Booked Scheduler abban a reményben kerül terjesztésre, hogy hasznos lesz,
ém MINDEN GARANCIA NÉLKÜL; még a KERESKEDELMI vagy GYAKORLATI FELHASZNÁLÁS
hallgatólagos garanciája nélkül.  További információt a 
GNU Általános Nyilvános Licenszben talál.

A Booked Scheduler mellett meg kellett kapja a GNU Általános Nyilvános Licensz egy példányát is.
Amennyiben nem, kérjük látogassa meg az alábbi oldalt <http://www.gnu.org/licenses/>.
*}
Ön nem jelentkezett be.<br/>
A foglalás részletei:
	<br/>
	<br/>
	kezdés: {formatdate date=$StartDate key=reservation_email}<br/>
	Befejezés: {formatdate date=$EndDate key=reservation_email}<br/>
	Elem: {$ResourceName}<br/>
	Megnevezés: {$Title}<br/>
	Leírás: {$Description|nl2br}
    {if $IsAutoRelease}
        <br/>
        Amenyyiben nem jelentkezik be, ez a foglalás automatikusan törlésre kerül ekkor: {formatdate date=$AutoReleaseTime key=reservation_email}
    {/if}
<br/>
<br/>
<a href="{$ScriptUrl}/{$ReservationUrl}">Ezen foglalás megtekintése</a> |
<a href="{$ScriptUrl}">Bejelentkezés ide: {$AppTitle}</a>
