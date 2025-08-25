<?php 
//web-based clipboard, for universal exchange between computers and other devices
//gus mueller, January 1 2023
 

function logIn() {
  Global $encryptionPassword;
  Global $cookiename;
  if(!isset($_COOKIE[$cookiename])) {
    return false;
  } else {
  
   $cookieValue = $_COOKIE[$cookiename];
   $email = openssl_decrypt($cookieValue, "AES-128-CTR", $encryptionPassword);
   if(strpos($email, "@") > 0){
      return getUser($email);
      
   } else {
      return  false;
   }
  }
}
 
function logOut() {
	Global $cookiename;
	setcookie($cookiename, "");
	return false;
}
 
function loginForm() {
  $out = "";
  $out .= "<form method='post' name='loginform' id='loginform'>\n";
  $out .= "<strong>Login here:</strong>  email: <input name='email' type='text'>\n";
  $out .= "password: <input name='password' type='password'>\n";
  $out .= "<input name='mode' value='login' type='submit'>\n";
  $out .= "<div> or  <div class='basicbutton'><a href=\"?mode=startnewuser\">Create Account</a></div>\n";
  $out .= "</form>\n";
  return $out;
}

function normalizePostData() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $_POST = $json;
        }
    }
} 

function newUserForm($error = NULL) {
	$formData = array(
		[
	    'label' => 'email',
		'name' => 'email',
	    'value' => gvfa("email", $_POST), 
		'error' => gvfa('email', $error)
	  ],
		[
	    'title' => 'password',
		'name' => 'password',
		'type' => 'password',
	    'value' => gvfa("password", $_POST), 
		'error' => gvfa('error', $error)
	   ],
		[
	    'label' => 'password (again)',
		'name' => 'password2',
		'type' => 'password',
	    'value' => gvfa("password2", $_POST),
		'error' => gvfa('password2', $error)
	   ]
	);
  return genericForm($formData, "create user");
}

function genericForm($data, $submitLabel) { //$data also includes any errors
	$out = "";
	$out .= "<form method='post' name='genericform' id='genericform'>\n";
	$out .= "<div class='genericform'>\n";
	foreach($data as &$datum) {
		$label = gvfa("label", $datum);
		$value = gvfa("value", $datum); 
		$name = gvfa("name", $datum); 
		$type =gvfa("type", $datum); 
		$error = gvfa("error", $datum); 
		if($label == "") {
			$label = $name;
		}
		if($type == "") {
			$type = "text";
		}
		$out .= "<div class='genericformelementlabel'>" . $label . ": </div><div class='genericformelementinput'><div class='genericformerror'>" . $error . "</div><input name='" . $name . "' value=\"" . addslashes($value) . "\" type='" . $type . "'/></div>\n";
	}
	$out .= "<div class='genericformelementlabel'><input name='mode' value='" .  $submitLabel. "' type='submit'/></div>\n";
	$out .= "</div>\n";
	$out .= "</form>\n";
	return $out;
}

function clipForm($typeId, $otherUserId) {
  $out = "";
  $out .= "<div>\n";
  $out .= "<form method='post' name='clipForm' id='clipForm'  enctype='multipart/form-data'>\n";
  $out .= "<div class='clipFormButtons'>\n";
  $out .= "<textarea name='clip' id='clip' style='width:500px; height:100px'>\n";
  $out .= "</textarea>\n";
  $onChange = "gotoSelectedClipType()";

  $out .= "</div>\n";
  $out .= "<div class='clipFormButtons'>\n";
  $out .= "<input type='file' id='clipfile' name='clipfile'>\n \n";
  $typeDropdown =  clipTypeDropdown($typeId, $onChange);
  if($typeDropdown) {
    $out .= "Type: " . $typeDropdown;
  }
  $out .= " Send to: " . userDropdown($otherUserId, "");
  $out .= "<input name='mode' value='Save Clip' type='submit'/>\n";
  $out .= "<input name='parent_clipboard_item_id' id='parent_clipboard_item_id' value='' type='hidden'/>\n";

  $out .= "</div>\n";
  $out .= "</form>\n";
  $out .= "</div>\n";
  return $out;
}



function userDropdown($default = "", $onChange = "", $jsId = "other_user_id") {
  global $conn, $user;
  $sql = "SELECT email as text, user_id as value  FROM user  WHERE user_id <> " . intval($user["user_id"]) . " ORDER BY email asc";
  $result = mysqli_query($conn, $sql);
  if($result) {
    $rows =  mysqli_fetch_all($result, MYSQLI_ASSOC);
    return genericSelect($jsId, $jsId, $default, $rows, "onchange", $onChange);
  }
}


function clipTypeDropdown($default = "", $onChange = "", $jsId = "clipboard_item_type_id") {
  global $conn, $user;
  $sql = "SELECT name as text, clipboard_item_type_id as value  FROM clipboard_item_type  WHERE user_id = " . intval($user["user_id"]) . " ORDER BY name asc";
  $result = mysqli_query($conn, $sql);
  if($result) {
    $rows =  mysqli_fetch_all($result, MYSQLI_ASSOC);
    return genericSelect($jsId, $jsId, $default, $rows, "onchange", $onChange);
  }
}

function genericSelect($id, $name, $defaultValue, $data, $event = "", $onChange) {
	$out = "";
	$out .= "<select name='" . $name. "' id='" . $id . "' " . $event . "=\"" . $onChange . "\">\n";
  $out .= "<option/>";
  if($data && count($data)) {
    foreach($data as $datum) {
      $value = gvfa("value", $datum);
      $text = gvfa("text", $datum);
      if($value == ""  && $text == ""){ //if it's just a list of items, then each is both $value and $text
        $value = $datum;
        $text = $datum;
      }
      if(!$text) { //if the array is just a list of scalar values:
        $value = $datum;
        $text = $datum;
      }
      $selected = "";
      if($defaultValue == $value) {
        $selected = " selected='true'";
      }
      $out.= "<option " . $selected . " value=\"" . $value . "\">";
      $out.= $text;
      $out.= "</option>";
    }
    $out.= "</select>";
    return $out;
	}
}

function getUser($email) {
  global $conn;
  $sql = "SELECT * FROM `user` WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'";
  //echo($sql);
  $result = mysqli_query($conn, $sql);
  $row = $result->fetch_assoc();
  //echo $row["email"];
  return $row;
}

function loginUser($source = NULL) {
  global $conn;
  global $encryptionPassword;
  global $cookiename;
  global $timezone;
  if($source == NULL) {
  	$source = $_REQUEST;
  }
  $email = gvfa("email", $source);
  $passwordIn = gvfa("password", $source);
  $sql = "SELECT `email`, `password` FROM `user` WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' ";
  //echo($sql);
  $result = mysqli_query($conn, $sql);
  $row = $result->fetch_assoc();
  if($row  && $row["email"] && $row["password"]) {
    $email = $row["email"];
    $passwordHashed = $row["password"];
    //for debugging:
    //echo crypt($passwordIn, $encryptionPassword);
    if (password_verify($passwordIn, $passwordHashed)) {
      //echo "DDDADA";
        setcookie($cookiename, openssl_encrypt($email, "AES-128-CTR", $encryptionPassword), time() + (30 * 365 * 24 * 60 * 60));
        header('Location: '.$_SERVER['PHP_SELF']);
        //echo "LOGGED IN!!!" . $email ;
        die;
    }
  }
  return false;
}


function createUser(){
  global $conn;
  global $encryptionPassword;
  global $timezone;
  $errors = NULL;
  $date = new DateTime("now", new DateTimeZone($timezone));//obviously, you would use your timezone, not necessarily mine
  $formatedDateTime =  $date->format("Y-m-d H:i:s"); 
  //echo $formatedDateTime . "<BR>";
  $password = gvfa("password", $_POST);
  $password2 = gvfa("password2", $_POST);
  $email = gvfa("email", $_POST);
  if($password != $password2 || $password == "") {
  	$errors["password2"] = "Passwords must be identical and have a value";
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors["email"] = "Invalid email format";
  }
  if(is_null($errors)) {
  	$encryptedPassword =  crypt($password, $encryptionPassword);
  	$sql = "INSERT INTO user(email, password, created) VALUES ('" . $email . "','" .  mysqli_real_escape_string($conn, $encryptedPassword) . "','" .$formatedDateTime . "')"; 
    //echo $sql;
    $result = mysqli_query($conn, $sql);
    $id = mysqli_insert_id($conn);
    $error = mysqli_error($conn);
    if($error) {
      echo  $error;
      return [$error];
    }
	//die("*" . $id);
  	loginUser($_POST);
	header("Location: ?");
  } else {
  	return $errors;
  
  }
  return false;
 
}

function saveClip($userId, $clip, $clipboard_item_type_id, $otherUserId=0, $parentClipboardItemId=0){
  global $conn, $timezone;
  if(!$otherUserId) {
    $otherUserId = 0;
  }
  if(!$parentClipboardItemId) {
    $parentClipboardItemId = 0;
  }
  $tempFile = $_FILES["clipfile"]["tmp_name"];
  $extension = "";
  $filename = "";
  if($tempFile) {
    $extension = pathinfo($_FILES["clipfile"]["name"], PATHINFO_EXTENSION);
    $filename = $_FILES["clipfile"]["name"];
  }
  $date = new DateTime("now", new DateTimeZone($timezone));//obviously, you would use your timezone, not necessarily mine
  $formatedDateTime =  $date->format('Y-m-d H:i:s'); 
  
  $sql = "INSERT INTO clipboard_item(user_id, type_id, clip, file_name, other_user_id, parent_clipboard_item_id, created) VALUES (" . $userId . "," .  intval($clipboard_item_type_id) . ",'" .  mysqli_real_escape_string($conn, $clip) . "','" . mysqli_real_escape_string($conn, $filename) . "'," . $otherUserId. "," . $parentClipboardItemId . ",'" .$formatedDateTime . "')"; 
  if($filename != "" || $clip != "") {
    $result = mysqli_query($conn, $sql);
    $id = mysqli_insert_id($conn);
    $targetDir = "uploads/";
    if($filename != "") {
      copy($tempFile, "./downloads/" . $id .  "." . $extension);
    }
  }
}

function clips($typeId) {
  global $conn, $user;
  global $encryptionPassword;
  $userId  = $user["user_id"];
  $sql = "SELECT *, i.created AS clip_created, u.email AS  other_email,  u.user_id As author_id    FROM `clipboard_item` i LEFT JOIN user u ON u.user_id=i.user_id LEFT JOIN user o on o.user_id=i.other_user_id WHERE i.user_id = " . $userId . " OR i.other_user_id = " . $userId;
 
  if($typeId) {
   $sql .= " AND i.type_id=" . intval($typeId); 
  }
  $sql .= " ORDER BY i.created DESC LIMIT 0,100";
  $out = "";
  $result = mysqli_query($conn, $sql);
  if($result) {
	  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
	  
	  if($rows) {
		  for($rowCount = 0; $rowCount< count($rows); $rowCount++) {
        $provideReply = false;
		    $row = $rows[$rowCount]; 
		    $out .= "<div class='postRow'>\n<div class='postDate'>" . $row["clip_created"];
        if($row["other_user_id"] == $userId) {
          $out .= "<br/>From: " . $row["other_email"];
          $provideReply = true;
		    } else if($row["other_user_id"] > 0) {
          $out .= "<br/>To: " . $row["other_email"];
		    }
		    $out .=  "</div>\n";
		    $clip = $row["clip"];
        $table = "clipboard_item";
        $pk = $table . "_id";
        //$calculatedHasedEntities = hash_hmac('sha256', $column . $table .$pk . $pkValue , $encryptionPassword);
		    $hashedEntities = hash_hmac('sha256', "type_id" . $table .$pk . $row[$pk] , $encryptionPassword);
		    $out .=  clipTypeDropdown($row["type_id"], "changeClipType(" . $row["clipboard_item_id"] . ",'" . $hashedEntities . "','type_" . $row["clipboard_item_id"] . "')","type_" . $row["clipboard_item_id"]);
		    if($clip != "") {
          
		      $out .= "<div class='clipTools'>" .  clipTools($row["clipboard_item_id"])   . "</div>\n";
		    } 
		    $out .= "<div  class='postClip'>\n";
		    $out .= "<span id='clip" . $row["clipboard_item_id"] . "'>";
		    
		    $endClip = "";
		    if(beginsWith($clip, "http")) {
		      $out .= "<a id='href" . $row["clipboard_item_id"] . "' href='" . $clip . "'>";
		      $endClip = "</a>";
		    } else {
				$out .= "<tt>";
				$endClip = "</tt>";
			}
		    $out .= str_replace("\n", "\n<br/>", $clip);
		    if($provideReply) {
          $out .= "<br/><button onclick='reply(" . $row["author_id"] . ",". $row["clipboard_item_id"] .")'>reply</button>";
        }
		    $out .=  $endClip;
		    $out .= "</span>";

		  
			$out .= "<span style='display:none' id='originalclip_" . $row["clipboard_item_id"] . "'>";
			$out .= $clip;

			$out .= "</span>";
		    if($row["file_name"] != "") {
		      $extension = pathinfo($row["file_name"], PATHINFO_EXTENSION);
		      $out .= "<div class='downloadLink'><a href='index.php?friendly=" . urlencode($row["file_name"]) . "&mode=download&path=" . urlencode("./downloads/" . $row["clipboard_item_id"] .  "." . $extension) . "'>" . $row["file_name"] . "</a>";
		      $out .= "</div>";
		    }
 
		    
		    
		    $out .= "</div>";
		    $out .= "</div>\n";
		  }
	  }
  }
  return $out;
}

function bodyWrap($content, $interface="") {
  $out = "";
  $out .= "<html>\n";
  $out .= "<head>\n";
  $out .= '<link rel="icon" type="image/x-icon" href="./favicon.ico" />' . "\n";
  
  
  if($interface == "app") {
    $out .= '<link rel="manifest" href="index.php?action=manifest&bgcolor=%23ffffff&icon=clipboard.png&themecolor=%2366cc66&name=Collab+Clipboard&shortname=ColabClip&url=https://randomsprocket.com/cb/app.php">' . "\n";
    $out .= '<meta name="mobile-web-app-capable" content="yes">' . "\n";
    $out .= '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
    $out .= '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
    $out .= '<link rel="icon" type="image/x-icon" href="./favicon.ico" />' . "\n";
    $out .= '
    <script>
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("/cb/sw.js")
        .then(() => console.log("Service worker registered"))
        .catch(err => console.error("Service worker registration failed:", err));
    }
    </script>';
  }
  $out .= "\n<script>let interface = '" . $interface . "';</script>" . "\n";

  $out .= "<script src='site.js?dfsdf'></script>\n";
  $out .= "<link rel='stylesheet' href='" . $interface  . "site.css'>\n";
  $out .= "<title>Web Clipboard</title>\n";
  $out .= "</head>\n";
  $out .= "<body>\n";
  $out .= $content;
  $out .= "</body>\n";
  $out .= "</html>\n";
  return $out;
}

//generate manifest JSON dynamically based on the location we are at, with the ability to subsitute in other icons, etc.
function manifestJson($name = "", $shortName = "", $startUrl = "dashoard.php", $icon = "lightbulb.png", $bgColor = "#ffffff", $themeColor = "#2196f3") {
  // Determine the current page URL path and query string
  // $startUrl = $_SERVER['REQUEST_URI'];
  // Build the manifest array
  $manifest = [
      "name" => $name,
      "short_name" => $shortName,
      "start_url" => $startUrl,
      "display" => "standalone",
      "background_color" => $bgColor,
      "theme_color" => $themeColor,
      "icons" => [
          [
              "src" => $icon,
              "sizes" => "192x192",
              "type" => "image/png"
          ],
          [
              "src" => $icon,
              "sizes" => "512x512",
              "type" => "image/png"
          ]
      ]
  ];
  // Set appropriate headers and output JSON
  header('Content-Type: application/json');
  echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  die();
}

function clipTools($clipId) {
  $out = "";
  $out .= "<a href='javascript:copy(" . $clipId . ")'> <img src='copy.png' height='10' border='0'/></a>\n";  
  return $out;
}

function gvfw($name, $fail = false){ //get value from wherever
  return gvfa($name, $_REQUEST, gvfa($name, $_POST, $fail));
}

function gvfa($name, $source, $fail = false){ //get value from associative
  if(isset($source[$name])) {
    return $source[$name];
  }
  return $fail;
}

function beginsWith($strIn, $what) {
//Does $strIn begin with $what?
	if (substr($strIn,0, strlen($what))==$what){
		return true;
	}
	return false;
}

function endsWith($strIn, $what) {
//Does $strIn end with $what?
	if (substr($strIn, strlen($strIn)- strlen($what) , strlen($what))==$what) {
		return true;
	}
	return false;
}

function download($path, $friendlyName){
    $file = file_get_contents($path);
    header("Cache-Control: no-cache private");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename='.$friendlyName);
    header("Content-Type: application/whatevs");
    header("Content-Transfer-Encoding: binary");
    header('Content-Length: '. strlen($file));
    echo $file;
    exit;
}