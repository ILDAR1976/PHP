<?php
include('./sender.php');
echo "<pre>";

$mail = new Sender();
$mail->test_send();

?>