<?php

require "../init.php";

use encryptorcode\authentication\AuthenticationManager as AuthenticationManager;

AuthenticationManager::getService()->logout();

?>

<h2>Logout page</h2>