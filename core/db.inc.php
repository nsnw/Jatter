<?php

// core/db.inc.php
//
// Functions for connecting to a DB. Uses ADODb.

// include ADODb functions
require_once('adodb/adodb.inc.php');

// define DB variables

// function to set up DB connections
function db_create()
{
	// inherit DB vars

	global $db_type;
	global $db_host;
	global $db_user;
	global $db_pass;
	global $db_name;

	// create ADODb connection and connect
	$conn = &ADONewConnection($db_type);
	$conn->PConnect($db_host, $db_user, $db_pass, $db_name);

	// if successful, return the connection link, otherwise return an error
	if($conn)
	{
		return $conn;
	}
	else
	{
		return "DBCONNFAILED";
	}
}


