<?php
define ('DIR', 'incomings/');
$MyDirectory = opendir(DIR);
$i = 0; $j = 0;
while($Entry = @readdir($MyDirectory) ) {
	if (preg_match("#[a-zA-Z0-9]+_[a-zA-Z0-9]+_[0-9]+\.up#", $Entry) ) {
		$path = DIR.$Entry;
		$time = file_get_contents($path);
		if (time() >= $time) {
			unlink($path);
			$i++;
		}
		$j++;
	}
}
echo $i.' of '.$j.' deleted.';