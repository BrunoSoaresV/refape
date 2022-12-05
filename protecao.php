<?php
session_start();
if(!isset($_SESSION['cnpj'])){
header("Location: login.php");
}
?>
