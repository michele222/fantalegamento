<?php 
include_once 'class/user.class.php';
include_once 'class/html.class.php';
include_once 'class/db.class.php';
include_once 'include/parameters.php';
include_once 'include/functions.php';
session_start();
if(!isset($_SESSION['user']) || !$_SESSION['user']->getStatus())
	header('Location: login.php');
else 
{
	$user = $_SESSION['user'];
	$user->refresh();
	$user->refreshAll();
}
?>