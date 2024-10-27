<?php
require_once '/var/www/private/configuration.php'; //uncomment this when pushing onto server
require_once '/var/www/private/utils.php';
//require_once 'configuration.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';
$session = new Session();
$session_id;
$loans = [];
$session_documents = [];

/* try
{
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
}
catch(Exception $e)
{
    echo $e->getMessage();
} */

$session->createSessionRequest();
$session->setSessionId();
$session_id = $session->session_id;
echo "\nsession created and set\n";


$query_Response = $session->queryFiles();
$response_msg = $query_Response->msg;
$file_names = extractFileNames($response_msg);
echo "\n$file_names\n";
//$db->insertDocuments($file_names);

echo "Terminating Session.\n";

$session->endSession();
?>