<?php
/*
 * Here, we build the API access:
 * The access infos are sent to this script with the server Signature.
 * If the Signature is correct, the access is created.
 */

// We need our configuration variables here
require_once '../config.php';

$https = ($_SERVER['SERVER_PORT'] == '443') ? 's' : ''; // Begin the building of the server url
$url = 'http'.$https.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']; // same as previous comment
$url = preg_replace("#mkdir.php$#", "", $url); // Delete the relative path to keep only the server name
$hash = hash_hmac('sha256', $content, PRIVATE_KEY); // Create the server Signature

// If the received hash is the same as the builded one
if (@$_GET['hash'] == $hash) {
	if (!file_exists('uploads/')) {
		mkdir('uploads');
	}
	
	if (preg_match("#^[a-zA-Z0-9_]+$#", $_GET['cid'])) {
		if (!file_exists('uploads/'.$_GET['cid'].'/')) {
			mkdir('uploads/'.$_GET['cid']);
			mkdir('uploads/'.$_GET['cid'].'/videos');
			echo 0;exit();
		}
	}
	
	echo 1;
}