<?php

require('core/db.inc.php');
require('core/msg.inc.php');
require('core/cmd.inc.php');
require('core/admin.inc.php');
require('core/chat.inc.php');

require('mods/remind.inc.php');
require('mods/rss.inc.php');

function jatter_debug($from, $body)
{
	echo "\n--[incoming message]-------\n";
	echo "FROM: $from\n";
	echo "BODY:\n..........\n$body\n..........\n";
}

function incoming_message($type, $packet)
{
	global $jabber;
	global $a_chatters;

	$from = $jabber->GetInfoFromMessageFrom($packet);
	$body = $jabber->GetInfoFromMessageBody($packet); 
	$jid = $jabber->StripJID($from);

	//jatter_debug($from, $body);

	// command checker
	if(substr($body, 0, 1) == "!")
	{
		// split command and args
		$firstspace = strpos($body, " ");
		if($firstspace == "")
		{
			$cmd = trim(substr($body, 1, strlen($body)-1));
		} else {
			$cmd = trim(substr($body, 1, $firstspace));
			$args = trim(substr($body, $firstspace+1, strlen($body)-$firstspace));
		}

		$output = jatter_cmd_exec($from, $cmd, $args);

		jatter_msg_chat($from, $output);
	}
	else
	{
		if(in_array($jid, $a_chatters))
		{
			jatter_chat_send($jid, $body);
		}
		else
		{
			jatter_msg_chat($from, "Use !help to see a list of commands.");
		}
	}
}

function Handler_presence_subscribe($message)
{
	global $jabber;
	global $version;

	$jid = $jabber->StripJID($jabber->GetInfoFromPresenceFrom($message));
	$jabber->SubscriptionAcceptRequest($jid);
	$jabber->Subscribe($jid);
	//TODO: (Handler_presense_subscribe) Change this to use jatter_msg_chat().
	jatter_msg_chat($jid, "Welcome to ".$version."\nSend !help for more info.");

	$admins = jatter_admin_list();

	if(!$admins)
	{
    		jatter_msg_chat($jid, "Since there are no admins configured at the moment, you have automatically been added as the first admin.");
		jatter_admin_add($jid);
	}
	else
	{
		jatter_admin_msg("ADMIN: ".$jid." has subscribed.");
	}
}

function Handler_presence_available($message)
{
	global $jabber;
	global $a_roster;

	$jid = $jabber->StripJID($jabber->GetInfoFromPresenceFrom($message));

	$a_roster[$jid]->status = "online";
}

function Handler_presence_unavailable($message)
{
	global $jabber;
	global $a_roster;

	$jid = $jabber->StripJID($jabber->GetInfoFromPresenceFrom($message));

	$a_roster[$jid]->status = "offline";
}

function Handler_iq_($message)
{
	global $jabber;

	// hell, I don't know what's going on - var_dump it.
	var_dump($message);
}
