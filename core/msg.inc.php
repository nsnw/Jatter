<?php

function jatter_msg_chat($to, $msg)
{
	global $jabber;

	// prepare message text
	$h_msg = htmlspecialchars($msg);
	$msg_len = strlen($h_msg);

	if($msg_len > 500)
	{
		$msg_proc = 0;
		$msg_pos = 0;

		while ( $msg_proc == 0 )
		{
			if(strlen($h_msg) < 500)
			{
				$pos = strlen($h_msg);
				$msg_proc = 1;
			}
			else
			{
				$pos = strpos($h_msg, "\n", 500);
			}
			$p_msg = substr($h_msg, 0, $pos);
			$h_msg = substr($h_msg, $pos, strlen($h_msg) - $pos);

			$message = array("body" => $p_msg);
			echo "--[sending message]-------\n";
			echo "TO : ".$to."\n";
			echo "MSG: ".$message['body']."\n";
			
			$jabber->SendMessage($to, "chat", NULL, $message);
		}
	}
	else
	{
		$message = array("body" => $h_msg);
		echo "--[sending message]-------\n";
		echo "TO : ".$to."\n";
		echo "MSG: ".$message['body']."\n";
		
		$jabber->SendMessage($to, "chat", NULL, $message);
	}
}
