<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';

try
{
    $date = date("Y-m-d H:i:s");
    echo currTime()." Querying all loans\nStarting at $date \n";
    $session = new Session();
    $session_id;
    $session->createSessionRequest();
    $session->setSessionId();
    $db_conn = new Db(DB_USER, DB_PASS, DB_NAME);
    
    echo "\n".currTime()." New Session Created\nSession id: $session->session_id\n";
    
    $query_response = $session->requestAllLoanIds();
    $response_msg = $query_response->msg;
    $loans = extractLoanIds($response_msg);
    $db_conn->insertLoans($loans);
    echo "\nDb ops complete\nTerminating Db connection...\n...\n";
    $db_conn->endDbConnection();
    
    
}
catch(Exception $e)
{
    echo $e->getMessage()."\n";
}
try
{
    $session->endSession();
    $date = date("Y-m-d H:i:s");
    echo "\n$date Session ended.\nexited.\n";
}
catch(Exception $e)
{
    echo currTime()." could not terminate session.";
    echo $e->getMessage()."\n";
}

?>