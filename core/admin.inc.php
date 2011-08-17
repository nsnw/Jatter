<?php

function jatter_admin_list()
{
	$db = db_create();

	$sql = "SELECT * FROM admins ORDER BY jid ASC";
	$rs = $db->Execute($sql);

	$a_getadmins = array();

        if($rs->_numOfRows != 0)
        {
		$admins = $rs->GetArray();
		
		for($a = 0; $a < $rs->_numOfRows; $a++)
		{
			$a_getadmins[$a] = $admins[$a]['jid'];
		}

		return $a_getadmins;
        }
	else
	{
		return FALSE;
	}
}

function jatter_admin_msg($msg)
{
	$admins = jatter_admin_list();

	for($a = 0; $a < count($admins); $a++)
	{
		jatter_msg_chat($admins[$a], $msg);
	}
}

function jatter_admin_check($jid)
{
	$db = db_create();

	$sql = "SELECT * FROM admins WHERE jid = '".$jid."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows != 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function jatter_admin_add($jid)
{
	$db = db_create();

	$check = jatter_admin_check($jid);

	if(!$check)
	{
		$admin_table = "admins";
		$admin_array = array("jid" => $jid);

		$sql = $db->GetInsertSQL($admin_table, $admin_array);
		$rs = $db->Execute($sql);

		//TODO: (jatter_admin_add) Check that admin user has been added.
		return "OK";
	}
	else
	{
		return "ALREADYADMIN";
	}
}

function jatter_admin_del($jid)
{
	$db = db_create();

	$check = jatter_admin_check($jid);

	if($check)
	{
		$sql = "DELETE FROM admins WHERE jid = '".$jid."'";
		$rs = $db->Execute($sql);

		//TODO: (jatter_admin_del) Check that admin user has been deleted.
		return "OK";
	}
	else
	{
		return "NOTADMIN";
	}
}

function jatter_admin_cmd($jid, $args)
{
	global $jabber;
	global $a_roster;

	$ret = jatter_cmd_arg($args, "4");

	$scmd = $ret['args']['0'];

	$admins = jatter_admin_list();
	if(in_array($jid, $admins))
	{

		switch ($scmd) {
			case "success":
				$jabber->Subscribe("jatter-success@jabber.netprojects.org.uk");
				$output = "Reported success! Thanks for helping out with Jatter!";
				break;
				
			case "auth":
				$jid = $ret['args']['1'];
				$jabber->SubscriptionAcceptRequest($jid);
				$output = "ADMIN: Sent authorisation to ".$jid;
				break;

			case "unauth":
				$jid = $ret['args']['1'];
				$jabber->SubscriptionDenyRequest($jid);
				$output = "ADMIN: Removed authorisation from ".$jid;
				break;

			case "sub":
				$jid = $ret['args']['1'];
				$jabber->Subscribe($jid);
				$output = "ADMIN: Requested subscription to ".$jid;
				break;

			case "unsub":
				$jid = $ret['args']['1'];
				$jabber->Unsubscribe($jid);
				$output = "ADMIN: Unsubscribed from ".$jid;
				break;

			case "status":
				$ret = jatter_cmd_arg($args, "2");
				$show = $ret['args']['1'];
				$msg = $ret['text'];

				$jabber->SendPresence("available", NULL, $msg, $show);
				$output = "ADMIN: Status set to ".$show."/ (".$msg.")";
				break;
				
			case "roster":
				$jabber->RosterUpdate();

				$output = "Roster:\n";
				for($a = 0; $a < count($jabber->roster); $a++)
				{
					$jid = $jabber->roster[$a]['jid'];
					$output .= $jid." - ".$a_roster[$jid]->status."\n";
				}

				break;
				
			case "say":
				$ret = jatter_cmd_arg($args, "2");
				jatter_msg_chat($ret['args']['1'], $ret['text']);
				break;
				
			case "broadcast":
				$ret = jatter_cmd_arg($args, "1");

				$jabber->RosterUpdate();

				for($a = 0; $a < count($jabber->roster); $a++)
				{
					jatter_msg_chat($jabber->roster[$a]['jid'], "BROADCAST (from ".$jid."):\n".$ret['text']);
				}

				$output = "Broadcast sent.";
				break;
			case "add":
				$ret = jatter_cmd_arg($args, "2");
				$test = jatter_admin_add($ret['args']['1']);
				if($test == "OK")
				{
					$output = $ret['args']['1']." has been given admin powers.";
				}
				else
				{
					$output = $ret['args']['1']." already has admin powers.";
				}
				break;

			case "del":
				$ret = jatter_cmd_arg($args, "2");
				$test = jatter_admin_del($ret['args']['1']);
				if($test == "OK")
				{
					$output = $ret['args']['1']." has had their admin powers removed.";
				}
				else
				{
					$output = $ret['args']['1']." does not already have admin powers.";
				}
				break;
				
			case "halt":
				$script_loc = $_SERVER['PWD'];

				jatter_msg_chat($jid, "Shutting down...");
				$jabber->Disconnect();

				exec($script_loc."/jatter stop");

				break;
				
			case "restart":
				$script_loc = $_SERVER['PWD'];

				jatter_msg_chat($jid, "Restarting...");
				$jabber->Disconnect();

				exec($script_loc."/jatter restart");

				break;
				
		}
	}

	return $output;
}
