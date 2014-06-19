<?php
/*
 * Run this script reguraly (currently on DreamVids, this script is launched every 24h)
 * to clear unused API accesses.
 * All expired accesses will be deleted by this script.
 */

define ('DIR', WORKING_DIR.'/incomings/'); // Define the accesses directory
$MyDirectory = opendir(DIR); // Open it
$i = 0; $j = 0; // Init counters

// For each file in thr directory DIR
while($Entry = @readdir($MyDirectory) ) {
	// If the current file is a API access file
	if (preg_match("#[a-zA-Z0-9]+_[a-zA-Z0-9]+_[0-9]+\.up#", $Entry) ) {
		$path = DIR.$Entry; // Set file path
		$time = file_get_contents($path); // Open'n'Read the file to get its expiration date (timestamp actually)
		// If the expiration date is reached
		if (time() >= $time) {
			unlink($path); // File deleted
			$i++; // Increase "deleted files" counter
		}
		$j++; // Increase "total files" counter
	}
}

// Worst UX in the whole world:
echo $i.' of '.$j.' deleted.';