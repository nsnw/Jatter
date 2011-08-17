<?php

function jatter_chat_cmd($jid, $args)
{
	global $a_chatters;
	global $s_chattopic;

	$ret = jatter_cmd_arg($args, "2");

	$scmd = $ret['args']['0'];
	
	switch ($scmd) {
		case "on":

			if(in_array($jid, $a_chatters))
			{
				$output = "You're already in chat mode! Use '!chat off' to leave.";
			}
			else
			{
				sort($a_chatters);
				$a_chatters[] = $jid;
				for($a = 0; $a < count($a_chatters); $a++)
				{
					$b = split("@", $a_chatters[$a]);
					$users .= $b[0]." ";
				}
				
				$output = "You're now in chat mode. Any messages you send that don't start with a '!' will be sent to the chat.\nCurrent topic: $s_chattopic\nUsers in the chat: $users";

				sort($a_chatters);
				for($a = 0; $a < count($a_chatters); $a++)
				{
					$chat_member = $a_chatters[$a];
					$disp_name = split("@", $jid);
					jatter_msg_chat($chat_member, "---> ".$jid." has joined the chat.");
				}
			}

			break;
		case "off":

			if(in_array($jid, $a_chatters))
			{
				sort($a_chatters);
				for($a = 0; $a < count($a_chatters); $a++)
				{
					if($a_chatters[$a] == $jid)
					{
						$a_chatters[$a] = "";
						$output = "Removed from chat. You will no longer recieve chat messages.";
						
						sort($a_chatters);
						for($a = 0; $a < count($a_chatters); $a++)
						{
							$chat_member = $a_chatters[$a];
							$disp_name = split("@", $jid);
							jatter_msg_chat($chat_member, "<--- ".$jid." has left the chat.");
						}
					}
				}
			}
			else
			{
				$output = "You're not in chat mode!";
			}

			break;
	}
	
	return $output;

}

function jatter_chat_send($jid, $body)
{
	global $a_chatters;
	global $s_chattopic;

	// check to see if we're receiving a chat command
	$cmd_char = substr($body, 0, 1);
	$disp_name = split("@", $jid);

	if($cmd_char == "[")
	{
		$chat_cmd_parts = split(" ", $body);
		$chat_cmd = substr($chat_cmd_parts[0], 1, strlen($chat_cmd_parts[0])-1);
		$chat_rest = substr($body, strlen($chat_cmd_parts[0])+1, strlen($body)-(strlen($chat_cmd_parts[0])+1));

		switch ($chat_cmd) {
			case "me":
				$chat_output = "* ".$disp_name[0]." ".$chat_rest;
				break;
			case "topic":
				$s_chattopic = "[".$disp_name[0]."] ".$chat_rest;
				$chat_output = "--- ".$disp_name[0]." has set the topic to: ".$chat_rest." ---";
				break;
			case "list":
				sort($a_chatters);
				$s_chatters = "";
				for($a = 0; $a < count($a_chatters); $a++)
				{
					$s_chatter = split("@", $a_chatters[$a]);
					$s_chatters .= $s_chatter[1]." ";
				}
				jatter_msg_chat($jid, "Users in the chat: ".$s_chatters);
				break;
		}
	}
	else
	{
		$chat_output = "<".$disp_name[0]."> ".$body;
	}

	if(strlen($chat_output) > 0)
	{
		sort($a_chatters);
		for($a = 0; $a < count($a_chatters); $a++)
		{
			$chat_member = $a_chatters[$a];
			jatter_msg_chat($chat_member, $chat_output);
		}
	}
}

