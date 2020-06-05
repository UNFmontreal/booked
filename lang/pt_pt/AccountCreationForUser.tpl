{*
Copyright 2011-2020 Nick Korbel, Paul Menchini

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
*}
<p>{$FullName},</p>

<p>Uma conta em {$AppTitle} foi criada para si com os seguintes detalhes:<br/>
Email: {$EmailAddress}<br/>
Nome: {$FullName}<br/>
Telefone: {$Phone}<br/>
Organização: {$Organization}<br/>
Posição: {$Position}<br/>
Senha: {$Password}</p>
{if !empty($CreatedBy)}
	Criado por: {$CreatedBy}
{/if}

<a href="{$ScriptUrl}">Entrar em {$AppTitle}</a>