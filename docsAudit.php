<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';

function updateAllDocuments($session, $db)
{
    $missing_documents = [];
    $all_docs_response = $session->requestAllDocuments();
    if(!$all_docs_response)
    {
        throw new Exception("Error: bad response. Could not get files\n");
    }
    $response_msg = $all_docs_response->msg;
    $file_names = extractFileNames($response_msg);
    $formatted_file_names = [];
    if(!$file_names || null)
    {
        throw new Exception("Could not get files from api\n");
    }
    foreach($file_names as $raw_file_name)
    {
        $formatted_file_names[] =  "('$raw_file_name')";
    }
    $outstanding_docs = $db->insertDocuments($formatted_file_names, "cron", true);
    foreach($outstanding_docs as $file)
    {
        try{
            [$loan_number, $doc_type, $file_name, $formatted_datetime] = extractDataFromFileName($file['file_name']);
            $missing_documents[] = [$loan_number, $doc_type, $file_name, $formatted_datetime];
        }
        catch(Exception $e)
        {
            echo "Error: $file has bad format\n";
            echo currTime()." ".$e->getMessage();
        }
    }
    echo currTime()." inserting documents\n";
    $db->insertDocuments($missing_documents);
}

try
{
    echo currTime()." Starting queryDocs job\n\n##################\n";
    $session = new Session();
    $session_created = $session->createSessionRequest();
    echo currTime()." Session id: $session->session_id\n";
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    if($session_created === false)
    {
        throw new Exception("Error: Session not created\n\n");
    }
    $session_id = $session->session_id;
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage();
    exit();
} 

try
{

    echo currTime()." Checking for missing documents\n";
    updateAllDocuments($session, $db);
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage();
}
$session->endSession();
$db->endDbConnection();
echo currTime()." Job Finished\n\n##################\n";
?>