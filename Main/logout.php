<?php
    session_start();
    session_destroy();
    header("location:../Home/home.php");
?>