<?php
// Constants definition
define('ROOT', '/'); // Your disk/partition root path where you want to store data
define('ALERT_EMAIL_ADDRESS', 'you@domain.tld'); // Set your mail here, to receive alerts
define('UNIT', 1000000000); // GB (1 > B; 1000 > KB; 1000000 > MB; 1000000000 > GB; etc.)
define('CRITICAL_FREE_SPACE', 10 * UNIT); // Set your critical limit here

// Get free disk space
$free_space = disk_free_space(ROOT);

// API Logic
if ($free_space < CRITICAL_FREE_SPACE)
{
	// French
	mail(ALERT_EMAIL_ADDRESS, '[DV Storage] Seuil critique atteint sur '.$_SERVER['SERVER_ADDR'], 'Libre: '.($free_space / 1000000000).' Go');
	
	// English
	//mail(ALERT_EMAIL_ADDRESS, '[DV Storage] Critical limit reached on '.$_SERVER['SERVER_ADDR'], 'Free: '.($free_space / 1000000000).' GB');
	
	echo 'CRITICAL_ALERT';
}
else
{
	echo $free_space;
}

exit();