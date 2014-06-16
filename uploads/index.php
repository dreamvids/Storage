<?php
/*
 * This script is used to upload a file on the distant server where this API is deployed:
 * it receive the file, its infos and move it in the correct directory
 * However, you need to have a API access in order to upload the file
 * This access is created in the following script: incomings/index.php
 * 
 * You can easily alter this script for your personal needs:
 * - If you wouldn't do some sharing video platform, you can delete the conversion feature
 * - In the same vein, you can also delete the user-oriented approach of the API
 * These kind of change shouldn't generate errors.
 */

header("Access-Control-Allow-Origin: *"); // Allow XMLHTTPRequest from anywhere

// If AJAX need to send a "OPTIONS" request before the "POST" one
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	header('HTTP/1.1 200 OK'); // Respond OK
	exit(); // Stop the script
}

require_once '../config.php'; // We need our configuration variables here

// This is to avoid "Notice: Undefined index" errors
$fileId = (isset($_GET['fid']) ) ? $_GET['fid'] : '';
$userId = (isset($_GET['uid']) ) ? $_GET['uid'] : '';
$typeId = (isset($_GET['tid']) ) ? $_GET['tid'] : '';

// API Core: The UPLOAD !
function upload($userId, $fileId, $type='vid') {
	
	// If a file to upload was sent to the script
	if(isset($_FILES['fileInput']) ) {
		$name = $_FILES['fileInput']['name']; // Get its name
		$exp = explode('.', $name); // Get its extension: Part 1/2 > Split on the dot
		$ext = $exp[count($exp)-1]; // Get its extension: Part 2/2 > Take the last index of the table
		$path = $userId.'/'.$fileId.'.'.$ext; // Generate the file path
		
		// If the user directory doesn't exist
		if(!file_exists($userId) ) {
			mkdir($userId); // Let's create it !
		}
		move_uploaded_file($_FILES['fileInput']['tmp_name'], $path); // Upload the file, the easiest part.
		
		// If it's a video
		if ($type == 'vid') {
			system('sudo -u www-data convert.sh "'.escapeshellcmd(getcwd().'/'.$path).'"'); // Convert it
			/*
			 * For more infos about the DreamVids Video Convertion system
			 * please checkout the "VideoConvertion" repository
			 * on the same Github account
			 */
		}
	}
	
	return $path; // Return the file path in order to update the database on the master server
}


// Script Logic begin here
$filename = '../incomings/'.$fileId.'_'.$typeId.'_'.$userId.'.up'; // Build access file path from GET data

// If the file exist (ie if the request is authorized)
if (file_exists($filename) ) {
	$f = file_get_contents($filename); // Open the file (ie get its expiration date)
	
	// If access hasn't expired yet
	if (time() <= $f) {
		
		// If a file to upload was sent to the script
		if(isset($_FILES['fileInput'])) {
			$name = $_FILES['fileInput']['name']; // See line 22
			$explode = explode(".", $name); // See line 23/24
			$ext = strtolower($explode[count($explode)-1]); // See line 61
			
			// Set of accepted types and their corresponding extensions 
			$acceptedExts = array
			(
				'vid' => array('webm', 'mp4', 'm4a', 'mpg', 'mpeg', '3gp', '3g2', 'asf', 'wma', 'mov', 'avi', 'wmv', 'ogg', 'ogv', 'flv', 'mkv'),
				'img' => array('jpeg', 'jpg', 'png', 'gif', 'tiff', 'svg')
			);
			
			// Set of conditons for each accepted types
			if (in_array(strtolower($ext), $acceptedExts['vid']) ) {
				$type = 'vid';
			}
			elseif (in_array(strtolower($ext), $acceptedExts['img']) ) {
				$type = 'img';
			}
			else {
				$type = false;
			}
			
			// If the file belongs to the set of accepted types
			if($type !== false) {
				$path = upload($userId, $fileId, $type); // Let's upload it !
				$https = ($_SERVER['SERVER_PORT'] == '443') ? 's' : ''; // Begin to build final'n'absolute path
				$url = 'http'.$https.'://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']; // Absolute path to the file
				$url = preg_replace("#uploads/.+$#", "", $url); // Delete the relative path to keep only the server name
				$hash = hash_hmac('sha256', $url, PRIVATE_KEY); // Build the server Signature
				
				// Finaly, call the master server in order to update infos in the database
				file_get_contents('http://'.MASTER_SERV.'/utils/add_db_infos.php?tid='.$typeId.'&sid='.SERVER_ID.'&fid='.$fileId.'&uid='.$userId.'&url='.urlencode($url.'uploads/'.$path).'&hash='.$hash);
			}
		}
	}
	else {
		// Access expired
		header('HTTP/1.1 403 Forbidden');
		echo '<h1>403 Forbidden</h1>';
	}
	
	unlink($filename); // We've done. Let's delete the API access.
}
else {
	// There is no access
	header('HTTP/1.1 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
}