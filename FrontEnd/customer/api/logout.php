<?php
session_start();
session_destroy();
header("Location: ../index.html"); // Or wherever the landing page is
exit();
?>