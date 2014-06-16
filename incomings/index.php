<?php
/*
 * Here, we build the API access:
 * The access infos are sent to this script with the server Signature.
 * If the Signature is correct, the access is created.
 */

// We need our configuration varaibles here
require_once '../config.php';

$https = ($_SERVER['SERVER_PORT'] == '443') ? 's' : ''; // Begin the building of the server url
$url = 'http'.$https.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']; // same as previous comment
$url = preg_replace("#incomings/.+$#", "", $url); // Delete the relative path to keep only the server name
$hash = hash_hmac('sha256', $url, PRIVATE_KEY); // Create the server Signature

// If the received hash is the same as the builded one
if (@$_GET['hash'] == $hash) {
	// Create the API access for these particular user, type and filename
	file_put_contents($_GET['fid'].'_'.$_GET['tid'].'_'.$_GET['uid'].'.up', time()+86400);
}
else {
	// The hash doesn't match, the request doesn't come from the master server.
	header('HTTP/1.1 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
}