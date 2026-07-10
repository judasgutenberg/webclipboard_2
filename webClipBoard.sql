	CREATE TABLE `clipboard_item` ( 
    `clipboard_item_id` int(11) NOT NULL AUTO_INCREMENT, 
    `user_id` int(11) DEFAULT NULL, 
	`other_user_id` int(11) NULL,
    `clip` text DEFAULT NULL, 
    `created` datetime DEFAULT NULL, 
    `altered` datetime DEFAULT NULL, 
    `file_extension` varchar(10) DEFAULT NULL, `
    file_name` varchar(250) DEFAULT NULL, 
    `is_private` tinyint(4) DEFAULT 1, 
    `is_private_for_other_user` tinyint(4) DEFAULT 0, 
    `type_id` int(11) DEFAULT NULL, `other_user_id` int(11) DEFAULT NULL, 
    `parent_clipboard_item_id` int(11) DEFAULT NULL, 
    `modified_user_id` int(11) DEFAULT NULL,
      PRIMARY KEY (`clipboard_item_id`) 
    ) 

CREATE TABLE `clipboard_item_type` ( 
  `clipboard_item_type_id` int(11) NOT NULL AUTO_INCREMENT, 
  `name` varchar(200) DEFAULT NULL, 
  `created` datetime DEFAULT NULL, 
  `is_public` tinyint(4) DEFAULT NULL, 
  `user_id` int(11) DEFAULT NULL, 
  PRIMARY KEY (`clipboard_item_type_id`) ) 

CREATE TABLE user(
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(200) NULL,
  sort_name VARCHAR(200) NULL,
  email VARCHAR(100) NULL,
  password VARCHAR(100) NULL,
  role VARCHAR(50) DEFAULT 'normal',
  expired DATETIME NULL,
  created DATETIME
);
