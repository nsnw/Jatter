<?php

function jatter_cmd_arg($args, $num)
{
	// splits args and text

	$arg = split(" ", $args);

	$a_args = array();

	for($a = 0; $a < $num; $a++)
	{
		$a_args[$a] = $arg[$a];
	}

	$text = "";

	for($a = $num; $a < count($arg); $a++)
	{
		$text .= $arg[$a]." ";
	}

	$ret = array("args" => $a_args, "text" => $text);

	return $ret;
}

function jatter_cmd_exec($from, $cmd, $args)
{
	// things we don't want in our commands that call external scripts
	$shellcmds = array(";", "`", ">", "<", "|");
	$jid_r = $from;

	global $jabber;
	global $a_roster;
	global $admins;
	global $a_chatters;
	
	$jid = $jabber->StripJID($jid_r);

	switch($cmd) {
		case "admin":
			$output = jatter_admin_cmd($jid, $args);
			break;
			
		case "feed":
			$output = jatter_rss_cmd($jid, $args);
			break;
			
		case "remind":
			$output = jatter_remind_cmd($jid, $args);
			break;
					
		case "chat":
			$output = jatter_chat_cmd($jid, $args);
			break;

		case "date":
			$output = "Local time is: ".system("date").".";
			break;
			
		case "uptime":
			$started = system("cat .started");
			$since = date("d-M-Y H:i:s", $started);
			$output = "I've been running since ".$since.".";
			break;
			
		case "support":
			jatter_admin_msg("*** ".$jid." requires your support ***");
			$output = "Support request sent.";
			break;
			
		case "help":
			global $version;
			
			$output = "Welcome to ".$version."
			see http://jatter.sourceforge.net/ for more info.
			
			Commands are:-
			(tools)
			!remind add [<date>] <time> <what>	- set a reminder
			!remind del <id>			- delete a reminder
			!remind list				- see your reminders
			!feed list			- see RSS feeds
			!feed add <name> <url>		- add an RSS feed
			!feed sub <name>		- subscribe to an RSS feed
			!feed unsub <name>		- unsubscribe from an RSS feed
			
			(chat)
			!chat on			- join the chat
			!chat off			- leave the chat

			(misc)
			!uptime				- uptime
			!date				- date & time
			
			(help)
			!help				- this help
			!support			- page the admin(s)
			
			";
			break;

		default:
			$output = "Not implemented.";
	}

	return $output;
}
