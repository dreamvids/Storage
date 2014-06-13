<?php
require_once '../config.php';
$https = ($_SERVER['SERVER_PORT'] == '443') ? 's' : '';
$url = 'http'.$https.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
$url = preg_replace("#incomings/.+$#", "", $url);
$hash = hash_hmac('sha256', $url, PRIVATE_KEY);

if (@$_GET['hash'] == $hash)
{
	file_put_contents($_GET['fid'].'_'.$_GET['tid'].'_'.$_GET['uid'].'.up', time()+86400);
}
else
{
	header('HTTP/1.1 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
}