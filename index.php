<?php
session_start();
include_once("configuration/Configuration.php");
include_once("helper/Permisos.php");
$configuration = new Configuration();

$router = $configuration->getRouter();

$rout = isset($_GET['action']) ? $_GET['action'] : " ";
$rout1 = isset($_GET['page']) ? $_GET['page'] : " ";

$router->route($rout1, $rout);


