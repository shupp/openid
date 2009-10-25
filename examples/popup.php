<?php

require_once 'common/config.php';

if (isset($_SESSION['popup_redirect'])) {
    $redirect_url = $_SESSION['popup_redirect'];
    unset($_SESSION['popup_redirect']);
    header("Location: $redirect_url");
}
?>
