<?php 
//web-based clipboard, for universal exchange between computers and other devices
//gus mueller, January 1 2023
//////////////////////////////////////////////////////////////

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");
include("cb_functions.php");

$date = new DateTime("now", new DateTimeZone($timezone));//set the $timezone global in config.php
$formattedDateTime =  $date->format('Y-m-d H:i:s');

normalizePostData();

$conn = mysqli_connect($servername, $username, $password, $database);

$mode = "";
 
$user = logIn();
$out = "";
$createUserErrors = NULL;
//$formatedDateTime =  $date->format('H:i');
$typeId = gvfw("type_id");
$action = gvfw("action");
$otherUserId = gvfw("other_user_id");
$parentClipboardItemId = gvfw("parent_clipboard_item_id");
if ($action == "manifest") {
    $startUrl = gvfw("url");
    $icon = gvfw("icon");
    $bgColor = gvfw("bgcolor");
    $themeColor = gvfw("themecolor");
    $name = gvfw("name");
    $shortName = gvfw("shortname");
    manifestJson($name, $shortName, $startUrl, $icon, $bgColor, $themeColor);
    die(); 
}
if(gvfw("mode")) {
 
  $mode = gvfw('mode');
  if ($mode == "logout") {
  	logOut();
    header("Location: ?mode=login");
    die();
  }
  
 
	if ($mode == "login") {
		loginUser();
	} else if (strtolower($mode) == "create user") {
		$createUserErrors = createUser();
	} else if (strtolower($mode) == "save clip" && $user != false) {
    
		saveClip($user["user_id"], gvfw("clip", ""), gvfw("clipboard_item_type_id", ""), $otherUserId, $parentClipboardItemId);
 
	
	} else if ($mode == "Save Clip" && $user != false) {
	
	
	} else if ($mode == "download" && $user != false) {
	

		$path = gvfw("path");
		$friendly = gvfw("friendly");
		download($path, $friendly);
		die();
		
	} else if ($mode == "json"  && $user) {

    $table = gvfw("table");
    $pk = gvfw("pk");
    $lastPkValue = gvfw($pk);
    $value =  gvfw("value");
    $specificItemId =  gvfw("specific_item_id");
    $limit =  100;
    $uptoDateDate = gvfw("uptodate_date");
    $hashedEntities = gvfw("hashed_entities");
    	
    //$calculatedHasedEntities = hash_hmac('sha256',$table .$pk, $encryptionPassword);
    //if($hashedEntities == $calculatedHasedEntities) {
      if($table == "clipboard_item") {
 
        $userId  = $user["user_id"];
        $sql = "SELECT *, i.created AS clip_created, o.email AS  other_email, u.email AS  author_email,   u.user_id As author_id ," .  $user["user_id"]  ." AS our_user_id   FROM `clipboard_item` i LEFT JOIN user u ON u.user_id=i.user_id LEFT JOIN user o on o.user_id=i.other_user_id WHERE (i.user_id = " . $user["user_id"]. " OR i.other_user_id = " . $user["user_id"] . ")";
        if($specificItemId) {
          $sql .=  " AND clipboard_item_id = " . intval($specificItemId);
        } else {
          $sql .=  " AND clipboard_item_id> " . intval($lastPkValue);
        }
        if($value) {
         $sql .= " AND i.type_id=" . intval($value); 
        }
      }
      $sql .= " ORDER BY i.created DESC LIMIT 0,100";
      $out = "";
      //echo $sql;
      //die();
      $result = mysqli_query($conn, $sql);
      $out = [];
      if($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach($rows as &$row) {
          //$calculatedHasedEntities = hash_hmac('sha256', $column . $table .$pk . $pkValue , $encryptionPassword);
          $hashedEntities = hash_hmac('sha256', "type_id" . $table .$pk . $row[$pk] , $encryptionPassword);
          $row["hashed_entities"] = $hashedEntities;
          $row["password"] = "";
        }
        $out["clips"]= $rows;
        
        //also populate other_modified
        $sql = "SELECT clipboard_item_id FROM clipboard_item WHERE other_user_id = " . intval($user["user_id"]) . " AND altered > '" . $uptoDateDate . "'";
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $otherModifiedIds = [];
         if($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach($rows as &$row) {
              $otherModifiedIds[] = $row["clipboard_item_id"];
            }
         }
         $out["other_modified"]= $otherModifiedIds;
        
        
        
        //probably not very DRY:
        $sql = "SELECT name as text, clipboard_item_type_id as value  FROM clipboard_item_type  WHERE user_id = " . intval($user["user_id"]) . " ORDER BY name asc";
        $result = mysqli_query($conn, $sql);
        if($result) {
          $rows =  mysqli_fetch_all($result, MYSQLI_ASSOC);
          $out["clipTypes"]= $rows;
        }
    
        
        die(json_encode($out));
      }
    
    //}
	} else if ($mode == "crud"  && $user) {
    $out = array();
    $table = gvfw("table");
    $pk = gvfw("pk");
    $pkValue = gvfw($pk);
    $column = gvfw("column");
    $value =  gvfw("value");
    $hashedEntities = gvfw("hashed_entities");
    //echo $column . $table .$pk . $pkValue  . "<BR>";
    $calculatedHasedEntities = hash_hmac('sha256', $column . $table .$pk . $pkValue , $encryptionPassword);
    //echo $hashedEntities . "  " .$calculatedHasedEntities;
    //die();
    if($hashedEntities == $calculatedHasedEntities) {
      if($action == "update") {
        $sql = "UPDATE " . $table . " SET " . $column . " = '" .$value . "' WHERE " . $pk . "='" .  $pkValue . "' AND user_id=" . $user["user_id"];
      }
      //die($sql);
      $result = mysqli_query($conn, $sql);
      die($value );
      //$row = $result->fetch_assoc();
    
    }
 
    if($action == "get") {
      if($table == "clipboard_item") {
        //yikes! need to make this haxor-proof
        $sql = "SELECT * FROM " . filterStringForSqlEntities($table) . " WHERE " . filterStringForSqlEntities($pk) . "='" . intval($pkValue) . "'";
        //echo $sql;
        $result = mysqli_query($conn, $sql);
        if($result) {
          $rows =  mysqli_fetch_all($result, MYSQLI_ASSOC);
          $out["sql"]= $sql;
          $out["clip"]= $rows[0];
          die(json_encode($out));
        }
      
      }
    }
    if($action == "update") {
      if($table == "clipboard_item") {
        //yikes! need to make this haxor-proof. well at least only the user or other user can change
        $sql = "UPDATE  " . filterStringForSqlEntities($table) . " SET modified_user_id=" . $user["user_id"] . ", altered='" . $formattedDateTime . "', " . filterStringForSqlEntities($column)  . "='" . mysqli_real_escape_string($conn, $value) . "' WHERE " . filterStringForSqlEntities($pk) . "='" . intval($pkValue) . "'";
        $sql .= " AND user_id=" . $user["user_id"] . " OR other_user_id=" . $user["user_id"];
        $result = mysqli_query($conn, $sql);
        $out["sql"]= $sql;
        die(json_encode($out));
      }
    }
    
    
	}
}

 



if($user) {
	$out .= "<div class='loggedin'>You are logged in as <b>" . $user["email"] . "</b> <div class='basicbutton'><a href=\"?mode=logout\">logout</a></div></div>\n"; 
	$out .= "<div>\n";
	$out .= clipForm($typeId, "");
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

echo bodyWrap($out);

 
 
 