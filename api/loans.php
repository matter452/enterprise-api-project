<?php
include_once('/var/www/private/Db.php');
include_once('/var/www/private/request.php');
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['PATH_INFO'];
$queryString = $_SERVER['QUERY_STRING'];
header('Content-Type: application/json');

switch($method)
{
    case 'GET':
        $db = new Db(DB_USER, DB_PASS, DB_NAME);
        if($endpoint === '/loans')
        {
            $_GET['loan_number'];
            $_GET['loan_doc_select'];
        }
        
}

?>