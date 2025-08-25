<?php 
//web-based clipboard, for universal exchange between computers and other devices
//gus mueller, January 1 2023
//////////////////////////////////////////////////////////////

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");
include("cb_functions.php");
normalizePostData();

$conn = mysqli_connect($servername, $username, $password, $database);

$mode = "";
 
$user = logIn();
$out = "";
$createUserErrors = NULL;
//$formatedDateTime =  $date->format('H:i');
$typeId = gvfw("type_id");
$action = gvfw("action");
$mode = gvfw('mode');
$otherUserId = gvfw("other_user_id");
$parentClipboardItemId = gvfw("parent_clipboard_item_id");

 
if(gvfw("mode")) {
 
  
  if ($mode == "logout") {
  	logOut();
    header("Location: ?mode=login");
    die();
  }
  
 
  if ($mode == "login") {
    loginUser();
  }
}



if($user) {
	//out .= "<div class='loggedin'>You are logged in as <b>" . $user["email"] . "</b> <div class='basicbutton'><a href=\"?mode=logout\">logout</a></div></div>\n"; 
	$out .= "<div>\n";
	$out .= appClipForm($typeId, "");
	$out .= "</div>\n";
	$out .= "<div id='clips'>\n";
	//$out .= clips($typeId);
	$out .= "</div>\n";
} else if ($mode == "startnewuser" || !is_null($createUserErrors)) {
	$out .= "<div class='loggedin'>You are logged out. <div class='basicbutton'><a href=\"?mode=login\">log in</a></div></div>\n"; 
	$out .= newUserForm($createUserErrors);
} else {
  if(gvfa("password", $_POST) != "") {
    $out .= "<div class='genericformerror'>The credentials you entered have failed.</div>";
   }
  $out .= loginForm();

}

echo bodyWrap($out, "app");

function appClipForm($typeId, $otherUserId) {
  $out = "";
  $out .= "<div>\n";
  $out .= "<form method='post' name='clipForm' id='clipForm'  enctype='multipart/form-data'>\n";
  $out .= "<div class='clipFormButtons'>\n";
  $out .= "<textarea name='clip' id='clip' style='width:500px; height:50px'>\n";
  $out .= "</textarea>\n";
  $onChange = "gotoSelectedClipType()";

  $out .= "</div>\n";
  $out .= "<div class='clipFormButtons'>\n";
  $out .= "<input type='file' id='clipfile' name='clipfile'>\n \n";
  //$typeDropdown =  clipTypeDropdown($typeId, $onChange);
  //if($typeDropdown) {
    //$out .= "Type: " . $typeDropdown;
  //}
  $out .= " Send to: " . userDropdown($otherUserId, "");
  $out .= "<input name='mode' value='Save Clip' type='submit'/>\n";
  $out .= "<input name='parent_clipboard_item_id' id='parent_clipboard_item_id' value='' type='hidden'/>\n";

  $out .= "</div>\n";
  $out .= "</form>\n";
  $out .= "</div>\n";
  return $out;
}
 
 