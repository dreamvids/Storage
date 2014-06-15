<?php
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	header('HTTP/1.1 200 OK');
	exit();
}

require_once '../config.php';

$fileId = (isset($_GET['fid']) ) ? $_GET['fid'] : '';
$userId = (isset($_GET['uid']) ) ? $_GET['uid'] : '';
$typeId = (isset($_GET['tid']) ) ? $_GET['tid'] : '';

function upload($userId, $fileId, $type='vid') {
	if(isset($_FILES['fileInput']) ) {
		$name = $_FILES['fileInput']['name'];
		$exp = explode('.', $name);
		$ext = $exp[count($exp)-1];
		$path = $userId.'/'.$fileId.'.'.$ext;
		if(!file_exists($userId) )
			mkdir($userId);
		move_uploaded_file($_FILES['fileInput']['tmp_name'], $path);
		if ($type == 'vid')
			system('sudo -u www-data convert.sh "'.escapeshellcmd(getcwd().'/'.$path).'"');
	}
	
	return $path;
}

$filename = '../incomings/'.$fileId.'_'.$typeId.'_'.$userId.'.up';
if (file_exists($filename) ) {
	$f = file_get_contents($filename);
	if (time() <= $f) {
		if(isset($_FILES['fileInput'])) {
			$name = $_FILES['fileInput']['name'];
			$explode = explode(".", $name);
			$ext = strtolower($explode[count($explode)-1]);
			$acceptedExts = array
			(
				'vid' => array('webm', 'mp4', 'm4a', 'mpg', 'mpeg', '3gp', '3g2', 'asf', 'wma', 'mov', 'avi', 'wmv', 'ogg', 'ogv', 'flv', 'mkv'),
				'img' => array('jpeg', 'jpg', 'png', 'gif', 'tiff', 'svg')
			);
			
			if (in_array(strtolower($ext), $acceptedExts['vid']) ) {
				$type = 'vid';
			}
			elseif (in_array(strtolower($ext), $acceptedExts['img']) ) {
				$type = 'img';
			}
			else {
				$type = false;
			}
			
			
			if($type !== false) {
				$path = upload($userId, $fileId, $type);
				$https = ($_SERVER['SERVER_PORT'] == '443') ? 's' : '';
				$url = 'http'.$https.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
				$url = preg_replace("#uploads/.+$#", "", $url);
				$hash = hash_hmac('sha256', $url, PRIVATE_KEY);
				file_get_contents('http://'.MASTER_SERV.'/utils/add_db_infos.php?tid='.$typeId.'&sid='.SERVER_ID.'&fid='.$fileId.'&uid='.$userId.'&url='.urlencode($url.'uploads/'.$path).'&hash='.$hash);
			}
		}
	}
	else {
		header('HTTP/1.1 403 Forbidden');
		echo '<h1>403 Forbidden</h1>';
	}
	unlink($filename);
}
else {
	header('HTTP/1.1 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
}