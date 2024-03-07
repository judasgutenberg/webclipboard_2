CREATE TABLE clipboard_item(
clipboard_item_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NULL,
other_user_id INT NULL,
parent_clipboard_item_id INT NULL,
clip TEXT NULL,
file_extension VARCHAR(10) NULL,
file_name VARCHAR(250) NULL,
type_id int NULL,
is_private TINYINT DEFAULT 1,
is_private_for_other_user TINYINT DEFAULT 0,
created DATETIME ,
altered DATETIME NULL
);


CREATE TABLE user(
user_id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(100) NULL,
password VARCHAR(100) NULL,
role VARCHAR(50) DEFAULT 'normal',
expired DATETIME NULL,
created DATETIME
);


CREATE TABLE type(
type_id INT AUTO_INCREMENT PRIMARY KEY,
type_name VARCHAR(100) NULL,
role VARCHAR(50) DEFAULT 'normal',
created DATETIME
);



--in case you have an older version of the webclipboard that you are trying to upgrade
/*
ALTER TABLE  clipboard_item add type_id INT NULL;
ALTER TABLE  clipboard_item add other_user_id INT NULL;
ALTER TABLE  clipboard_item add parent_clipboard_item_id INT NULL;
ALTER TABLE  clipboard_item add is_private TINYINT DEFAULT 1;
ALTER TABLE  clipboard_item add is_private_for_other_user TINYINT DEFAULT 0;
*/