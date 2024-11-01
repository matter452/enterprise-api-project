<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';

try
{
    $session = new Session();
    $session->createSessionRequest();
    $session->setSessionId();
    echo currTime()." Session id: $session->session_id\n";
    
    
    echo currTime()." Querying all loans\n";
    $query_response = $session->requestAllLoanIds();
    $response_msg = $query_response->msg;
    $loans = extractLoanIds($response_msg);
    $db_conn = new Db(DB_USER, DB_PASS, DB_NAME);
    $db_conn->insertLoans($loans);
    echo currTime()." Db operations finished.\n";
    $db_conn->endDbConnection();
    
    
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage()."\n";
}
try
{
    $session->endSession();
}
catch(Exception $e)
{
    echo currTime()." Error: Could not terminate session.\n";
    echo $e->getMessage()."\n";
}
echo currTime()." Job Finished";
?>