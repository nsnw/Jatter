<?php

include('lastrss.php');

function jatter_rss_today($url)
{
	$orss = new lastRSS;
	$rss = $orss->Get($url);
	$from = time()-86400;

	$item_count = count($rss['items']);
	$item = $rss['items'];

	$output = "";

	for($a = 0; $a < $item_count; $a++)
	{
		if($from < strtotime($item[$a]['pubDate']))
		{
			$output .= date("d-M-Y H:i", strtotime($item[$a]['pubDate']))." - ".$item[$a]['title']."\n - ".$item[$a]['link']."\n";
		}
	}

	return $output;
}

function jatter_rss_newest($url)
{
	$orss = new lastRSS;
	$rss = $orss->Get($url);

	$item_count = count($rss['items']);
	$item = $rss['items'];

	$output = "";

	$times = array();

	for($a = 0; $a < $item_count; $a++)
	{
		$times[$a] = strtotime($item[$a]['pubDate']);
	}

	rsort($times);

	return $times[0];
}

	

function jatter_rss_from($url, $from)
{
	$orss = new lastRSS;
	$rss = $orss->Get($url);
	
	$item_count = count($rss['items']);
	$item = $rss['items'];

	$output = "";

	for($a = 0; $a < $item_count; $a++)
	{
		if($from < strtotime($item[$a]['pubDate']))
		{
			$output .= date("d-M-Y H:i", strtotime($item[$a]['pubDate']))." - ".$item[$a]['title']."\n - ".$item[$a]['link']."\n";
		}
	}

	return $output;
}

function jatter_rss_list()
{
	$db = db_create();

	$sql = "SELECT * FROM feeds ORDER BY name ASC";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "No feeds available! Add one with '!feed add <name> <url>'.";
	}
	else
	{
		$feeds = $rs->GetArray();

		$output = "Feeds available:-";

		for($a = 0; $a < $rs->_numOfRows; $a++)
		{
			$output .= " * ".$feeds[$a]['name']."\n";
		}
	}

	return $output;
}

function jatter_rss_list_raw()
{
	$db = db_create();

	$sql = "SELECT * FROM feeds ORDER BY name ASC";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		return "NONE";
	}
	else
	{
		$feeds = $rs->GetArray();
		return $feeds;
	}

}

function jatter_rss_check($id, $from)
{
	$url = jatter_rss_get_byid($id);
	$newest = jatter_rss_newest($url);

	if($newest > $from)
	{
		return $newest;
	}
	else
	{
		return "NONE";
	}
}

function jatter_rss_subs_byid($id)
{
	$db = db_create();

	$sql = "SELECT * FROM feedsub WHERE feedid = '".$id."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows != 0)
	{
		$users = $rs->GetArray();
		
		return $users;
	}
	else
	{
		return "NOSUBS";
	}
}
			
function jatter_rss_subs_byjid($jid)
{
	$db = db_create();

	$sql = "SELECT * FROM feedsub WHERE jid = '".$jid."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "You're not subscribed to any feeds. Subscribe to one with '!feed sub <name>'.";
	}
	else
	{
		$feeds = $rs->GetArray();

		$output = "Feeds subscribed to:-";

		for($a = 0; $a < $rs->_numOfRows; $a++)
		{
			$output .= " * ".jatter_rss_id2name($feeds[$a]['feedid'])."\n";
		}
	}

	return $output;
}

function jatter_rss_get_byname($name)
{
	$db = db_create();

	$sql = "SELECT * FROM feeds WHERE name = '".$name."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "NOFEED";
	}
	else
	{
		$output = $rs->fields['url'];
	}

	return $output;
}

function jatter_rss_get_byid($id)
{
	$db = db_create();

	$sql = "SELECT * FROM feeds WHERE id = '".$id."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "NOFEED";
	}
	else
	{
		$output = $rs->fields['url'];
	}

	return $output;
}

function jatter_rss_id2name($id)
{
	$db = db_create();
	$sql = "SELECT * FROM feeds WHERE id = '".$id."'";
	
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "NOFEEDNAME";
	}
	else
	{
		$output = $rs->fields['name'];
	}

	return $output;
}

function jatter_rss_add($name, $url)
{
	$db = db_create();
	
	$query = array(
			"name" => $name,
			"url" => $url);

	$table = "feeds";

	$sql = $db->GetInsertSQL($table, $query);

	$rs = $db->Execute($sql);

	$sql = "SELECT * FROM feeds WHERE name = '".$name."' AND url = '".$url."'";

	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "Something broke. Consider sending a bug report.";
	}
	else
	{
		$output = "Added '".$name."'.";
	}

	return $output;
}

function jatter_rss_name2id($name)
{
	$db = db_create();
	$sql = "SELECT * FROM feeds WHERE name = '".$name."'";
	$rs = $db->Execute($sql);

	if($rs->_numOfRows == 0)
	{
		$output = "NOFEEDID";
	}
	else
	{
		$output = $rs->fields['id'];
	}

	return $output;
}

function jatter_rss_sub($jid, $name)
{
	$feed_id = jatter_rss_name2id($name);

	if($feed_id == "NOFEEDID")
	{
		$output = "There's no feed called '".$name."' - try '!feed list' to see a list of available feed.";
	}
	else
	{
		$db = db_create();
		$sql = "SELECT * FROM feedsub WHERE jid = '".$jid."' AND feedid = '".$feed_id."'";
		$rs = $db->Execute($sql);

		if($rs->_numOfRows == 0)
		{
			$table = "feedsub";
			$query = array("jid" => $jid, "feedid" => $feed_id);
			$sql = $db->GetInsertSQL($table, $query);
			
			$rs = $db->Execute($sql);
			echo $sql;
			
			$output = "Subscribed to '".$name."'. Use '!feed unsub ".$name."' to unsubscribe again.";
		}
		else
		{
			$output = "You're already subscribed to '".$name."'.";
		}
	}

	return $output;
}

function jatter_rss_unsub($jid, $name)
{
	$feed_id = jatter_rss_name2id($name);

	if($feed_id == "NOFEEDID")
	{
		$output = "There's no feed called '".$name."' - try '!feed list' to see a list of available feed.";
	}
	else
	{
		$db = db_create();
		$sql = "SELECT * FROM feedsub WHERE jid = '".$jid."' AND feedid = '".$feed_id."'";
		$rs = $db->Execute($sql);

		if($rs->_numOfRows != 0)
		{
			$sql = "DELETE FROM feedsub WHERE jid = '".$jid."' AND feedid = '".$feed_id."'";
			$rs = $db->Execute($sql);
			
			$output = "Unsubscribed from '".$name."'. Use '!feed sub ".$name."' to subscribe again.";
		}
		else
		{
			$output = "You're not subscribed to '".$name."'.";
		}
	}

	return $output;
}

function jatter_rss_run()
{

	global $f_check;
	global $f_check_time;
	global $f_newest;

	if(strlen($f_check) == "0")
	{
		$f_check = 1;
		$f_check_time = time();
		$f_newest = array();
	}

	if($f_check == 60)
	{
		$f_check = 0;
		$feeds = jatter_rss_list_raw();

		if($feeds != "NONE")
		{
			$num_feeds = count($feeds);

			for($a = 0; $a < $num_feeds; $a++)
			{
				$feed_id = $feeds[$a]['id'];
				$subs = jatter_rss_subs_byid($feed_id);

				if($subs != "NOSUBS")
				{
					$num_subs = count($subs);

					$feed_url = jatter_rss_get_byid($feed_id);

					if($f_newest[$feed_id] == "")
					{
						$f_newest[$feed_id] = time();
					}
					else
					{
						$check = jatter_rss_check($feed_id, $f_newest[$feed_id]);

						if($check != "NONE")
						{
							$newarticles = jatter_rss_from($feed_url, $f_newest[$feed_id]);

							for($b = 0; $b < $num_subs; $b++)
							{
								jatter_msg_chat($subs[$b]['jid'], $newarticles);
							}

							$f_newest[$feed_id] = $check;
						}
					}
				}
			}
		}
	}
	else
	{
		$f_check++;
	}

}

function jatter_rss_cmd($jid, $args)
{
	$ret = jatter_cmd_arg($args, "1");

	$scmd = $ret['args']['0'];

	switch($scmd) {
		case "list":
			$output = jatter_rss_list();

			break;
		case "read":
			$url = jatter_rss_get_byname($ret['text']);

			if($url != "NOFEED")
			{
				$output = jatter_rss_today($url);
			}
			else
			{
				$output = "No such feed! See a list of available feeds by typing '!feed list'\n";
			}
			break;
		case "add":
			$newret = jatter_cmd_arg($args, "3");

			$name = $newret['args']['1'];
			$url = $newret['args']['2'];

			if($name != "" && $url != "")
			{
				$output = jatter_rss_add($name, $url);
			}
			else
			{
				$output = "Oops. Add a feed with '!feed add <name> <url>'.";
			}
			break;
		case "sub":
			$name = $ret['text'];

			if($name == "")
			{
				$output = "No feed name given!. Do '!feed list' to see a list of available feeds.";
			}
			else
			{
				$output = jatter_rss_sub($jid, $name);
			}
			break;
		case "unsub":
			$name = $ret['text'];

			if($name == "")
			{
				$output = "No feed name given! Do '!feed sublist' to see the feeds you're subscribed to.";
			}
			else
			{
				$output = jatter_rss_unsub($jid, $name);
			}
			break;
		case "sublist":
			
			$output = jatter_rss_subs_byjid($jid);

			break;
			
	}

	return $output;
}
