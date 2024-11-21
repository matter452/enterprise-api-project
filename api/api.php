<?php
include_once('/var/www/private/Db.php');
include_once('/var/www/private/request.php');
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'];
$queryString = $_SERVER['QUERY_STRING'];
header('Content-Type: application/json');
?>