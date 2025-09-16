# WebClipBoard_2

This is a clipboard system to use primarily for transferring small amounts of text data (the sort that would be in a clipboard) between computers or other networked devices.  This is similar to other web-based clipboards, but the advantage is that the code would run on a server you control. This way you can keep your clips around indefinitely, revisit old clips, and even use it as a kind of cloud-based journal. It has a simple login system so that multiple people can use it and keep their clips separate.  In this way, it's like a Slack channel where you communicate with yourself.  This was a feature I used all the time on Slack, and when my employer switched to Microsoft Teams, I missed it.  (Microsoft Teams later implemented this functionality.)  The advantage with this system is that all you need is a web browser to use it and aren't dependent on a huge clunky Electron-based application that either needs to be installed or won't run at all (if you have older hardware).  

This version also supports simple two-way communication between two users in the system. You pick the target in the dropdown, and if you do, the clip appears in their clips as well (and it does so without them needing to reload).  To facilitate such useful two-way communication as shared shopping lists, it is possible for both the sender and receiver to edit an entry after it is posted. I've installed a copy of this as a progressive web app on the phone of my special-needs brother so I can easily send him photos to look at, as he doesn't seem to have the attention span necessary to absorb how to receive an email.

There are two front-ends, index.php for conventional web browsers, and app.php, which is designed to be installed as a progressive web-app on a smartphone. Once you've logged-in, the smartphone app is very handy for taking notes such as grocery lists or two-person text/image communications.

The clipboard also supports file uploads in addition to simple text and preserves the filename of the uploaded file. Uploaded images are rendered so you can see them in the clip.

Passwords are encrypted and new accounts are easily created (there is no email verification or easy UI to change your password!).

None of this code has pretenses of being any more than it is. It's very straightforward and imperative.  It has no dependencies and nothing needs compilation, because why would it?
Note: like all applications that store user data, this system uses cookies.

To use this, you will need to run the WebClipBoard.sql file on a MySQL server you control.  

Create new accounts from within the system so the passwords will be encrypted correctly. Otherwise you will need to encrypt the passwords using the PHP function crypt(YOUR_PASSWORD, $encryptionPassword) and then run the following from within MySQL:

INSERT INTO `user`(email, password, created) VALUES ('your@email.com', 'your_encrypted_password', '2023-01-01');

Then you will need to point to your database in a config.php file in the root with a structure based on this template:
<code>
&lt;?php

$servername = "localhost";
$username = "your_username";
$database = "your_database";
$password = "your_password";
$encryptionPassword = "your_cookie_encryption_password";
$cookiename = "your_cookie_name";
$timezone = "America/New_York";
 
?&gt;
</code>
To see this in action (and create your own clipboard hosted on my server until I stop paying the bills), go here:

http://randomsprocket.com/cb/index.php

If you do this, keep in mind that I can read your clipboard's contents, though your password is encrypted beyond my ability to recover it.
