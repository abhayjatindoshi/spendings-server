<?php
define("ROOT",dirname(__FILE__));
require ROOT."/vendor/autoload.php";
require ROOT."/src/internals/Autoloader.php";

define("SERVER_DOMAIN","{server.domain}");

// DB logins
define("DB_HOST","{db.host}");
define("DB_USERNAME","{db.username}");
define("DB_PASSWORD","{db.password}");
define("DB_NAME","{db.name}");

// Google Strategy
define('GOOGLE_CLIENT_ID','{google.client.id}');
define('GOOGLE_CLIENT_SECRET','{google.client.secret}');
