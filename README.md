Storage
=======

Decentralized storage API for DreamVids storage servers.
Install this API on a distant server and enjoy it ! It allow you to store data for your website or software on a distant server ;)

Step 1:
-------

Set the configurations variables in config.php


Step 2:
-------

Before the upload, call incomings/index.php with correct arguments to allow the future upload


Step 3:
-------

The submit URL of the upload must be uploads/index.php (via the action attribute of <form> elements or a cross-server XMLHTTPRequest)


Step 4:
-------

Don't forget to launch cron.php every day/week or whatever :)