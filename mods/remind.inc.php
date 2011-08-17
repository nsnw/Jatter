<?php

function jatter_remind_list($jid)
{
	$db = db_create();

	$sql = "SELECT * FROM remind WHERE jid = '".$jid."' and stamp > '".time()."' ORDER BY stamp ASC";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "You currently have no reminders.";
	}
	else
	{
		$num_reminders = $rs->_numOfRows;

		$output = "Reminders currently set for ".$jid.":-\n";

		$remind_array = $rs->GetArray();

		for($a = 0; $a < $num_reminders; $a++)
		{
			$output .= "#".$remind_array[$a]['id']." - ".date("d-M-Y H:i", $remind_array[$a]['stamp'])." - ".$remind_array[$a]['msg']."\n";
		}
	}

	return $output;
}

function jatter_remind_del($jid, $id)
{
	$db = db_create();

	$sql = "SELECT * FROM remind WHERE jid = '".$jid."' AND id = '".$id."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "No such reminder. Better luck next time!\n";
	}
	else
	{
		$sql = "DELETE FROM remind WHERE jid = '".$jid."' AND id = '".$id."'";
		$rs = $db->Execute($sql);

		$output = "Deleted reminder #".$id;
	}

	return $output;
}

function jatter_remind_range($from, $to)
{
	$db = db_create();

	$sql = "SELECT * FROM remind WHERE stamp >= '".$from."' AND stamp < '".$to."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows != 0)
	{
		return $rs->GetArray();
	}
	else
	{
		return "NONE";
	}
}

function jatter_remind_add($jid, $timestamp, $text)
{
	$db = db_create();

	$msg = addslashes($text);

	$query = array(
			"jid" => $jid,
			"stamp" => $timestamp,
			"msg" => $msg
			);

	$table = "remind";

	$sql = $db->GetInsertSQL($table, $query);
	$rs = $db->Execute($sql);

	$sql = "SELECT * FROM remind WHERE jid = '".$jid."' AND stamp = '".$timestamp."' AND msg = '".$msg."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		// Oh, the profanity!
		//$output = "Oh shit! Something's fucked! EVERYBODY PANIC!\n";
		$output = "Something broke. This isn't good. Consider opening a bug report.\n";
	}
	else
	{
		$output = "Reminder #".$rs->fields['id']." set for ".date("d-M-Y H:i", $timestamp).": '".$text."' set. Use '!remind list' to list your reminders.\n";
	}

	return $output;
}

function jatter_remind_run()
{
	global $jatter_remind_from;

	if($jatter_remind_from == "")
	{
		$jatter_remind_from = time();
	}

	$jatter_remind_to = time();

	$reminders = jatter_remind_range($jatter_remind_from, $jatter_remind_to);
	$jatter_remind_from = $jatter_remind_to;

	if($reminders != "NONE")
	{
		for($a = 0; $a < count($reminders); $a++)
		{
			jatter_msg_chat($reminders[$a]['jid'], "REMINDER: ".$reminders[$a]['msg']);
			jatter_remind_del($reminders[$a]['jid'], $reminders[$a]['id']);
		}
	}
}

function jatter_remind_cmd($jid, $args)
{
	$ret = jatter_cmd_arg($args, "2");

	$scmd = $ret['args']['0'];

	switch ($scmd) {
		case "list":
			$output = jatter_remind_list($jid);
			
			break;
		case "del":
			$output = jatter_remind_del($jid, $ret['args']['1']);

			break;
		case "add":
			if(strpos($ret['args']['1'], ":") > 0)
			{
				$timearg = split(":", $ret['args']['1']);

				$hour = $timearg['0'];
				$min = $timearg['1'];

				$timestamp = mktime($hour, $min, 0);

				$reminder = $ret['text'];

				$output = jatter_remind_add($jid, $timestamp, $reminder); 
			}
			else if (strpos($ret['args']['1'], "/") > 0)
			{
				$newret = jatter_cmd_arg($args, "3");

				$datearg = split("/", $newret['args']['1']);
				$timearg = split(":", $newret['args']['2']);

				$hour = $timearg['0'];
				$min = $timearg['1'];

				$day = $datearg['0'];
				$month = $datearg['1'];
				$year = $datearg['2'];

				$timestamp = mktime($hour, $min, 0, $month, $day, $year);

				$reminder = $newret['text'];

				$output = jatter_remind_add($jid, $timestamp, $reminder);
			}
			else
			{
				$output = "Oops. Invalid reminder format. Do '!remind add DD/MM/YYYY HH:MM <reminder>' or '!remind add HH:MM <reminder>'\n";
			}
			break;
	}

	return $output;
}
