#WebClipBoard

This version of WebClipBoad seeks to serve as more of communication system, facilitating the creation of things like blogs and messageboards.

To use this, you will need to run the WebClipBoard.sql file on a MySQL server you control.  

Create new accounts from within the system so the passwords will be encrypted correctly. Otherwise you will need to encrypt the passwords using the PHP function crypt(YOUR_PASSWORD, $encryptionPassword) and then run the following from within MySQL:

INSERT INTO `user`(email, password, created) VALUES ('your@email.com', 'your_encrypted_password', '2023-01-01');

Then you will need to point to your database in a config.php file in the root with this structure:

<?php

$servername = "localhost";
$username = "your_username";
$database = "your_database";
$password = "your_password";
$encryptionPassword = "your_cookie_encryption_password";
$cookiename = "your_cookie_name";

To see this in action (and create your own clipboard hosted on my server until I stop paying the bills), go here:

http://randomsprocket.com/cb/index.php

If you do this, keep in mind that I can read your clipboard's contents, though your password is encrypted beyond my ability to recover it.
