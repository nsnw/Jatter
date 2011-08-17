<?php

/*

Jatter v1.2 - Interactive chat bot for Jabber

Copyright (C) 2006 Andy Smith <andy.smith@netprojects.org.uk>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

require('config/conf.inc.php');
require('core/class.jabber.php');
require('core/handler.inc.php');

$version = "Jatter v1.2 - (c)2006 Andy Smith";

// Log the time the bot was started up
$stamp = time();
system("echo $stamp >.started");

// Create new class
$jabber = new Jabber;

// set up connection details
// account needs to be created prior to running jatter
$jabber->server		= $jatter_server;
$jabber->username	= $jatter_username;
$jabber->password	= $jatter_password;
$jabber->resource	= $jatter_resource;

if($jatter_debug == TRUE)
{
	$jabber->enable_logging = TRUE;
	$jabber->log_filename   = "./jabber.log";
}

// attempt to connect
$jabber->Connect() or die ("Couldn't connect to ".$jatter_server.".");

// attempt to authenticate
$test = $jabber->SendAuth();

print_r($test);

// if authentication fails...
if(!$test)
{
	// try and register an account
	$reg_test = $jabber->AccountRegistration();

	if($reg_test == "1")
	{
		die ("Username is already in use.");
	}
	else if($reg_test == "3")
	{
		die ("No response.");
	}
	else if($reg_test == "2")
	{
		echo ("Username/password registered. Reconnecting...");
		$test = $jabber->SendAuth();
		if(!$test)
		{
			die ("Something bad's happening. Try registering an account manually before running.");
		}
	}
	else
	{
		die ("Unspecified error.");
	}
		
}

// see where we are. send presence out and sync the roster
$jabber->SendPresence();
$jabber->RosterUpdate();

$a_admins = jatter_admin_list();
$a_chatters = array();

while ( 1 == 1 )
{
	$jabber->CruiseControl(5);

	jatter_remind_run();
	jatter_rss_run();

}

$jabber->Disconnect();
