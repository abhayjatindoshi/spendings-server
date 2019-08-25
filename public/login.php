<?php
require "../init.php";

encryptorcode\authentication\AuthenticationManager::getService()->loginPage();
?>
<script>
window.location = '/login?strategy=Google';
</script>