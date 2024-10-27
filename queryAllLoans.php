<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';

$session = new Session();
$session_id;
$session->createSessionRequest();
$session->setSessionId();
$db_conn = new Db(DB_USER, DB_PASS, DB_NAME);

echo "\nNew Session Created\nSession id: $session->session_id\n";

$query_response = $session->requestAllLoanIds();
$response_msg = $query_response->msg;
$loans = extractLoanIds($response_msg);
$db_conn->insertLoans($loans);
echo "\nDb ops complete\nTerminating Db connection...\n...\n";
$db_conn->endDbConnection();


$session->endSession();
echo "\nSession ended.\nexited.\n";
?>