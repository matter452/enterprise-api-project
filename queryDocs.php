<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';
try
{
    echo currTime()." Starting queryDocs job\n\n##################\n";
    $loans = [];
    $session_documents = [];
    $session = new Session();
    $session_id;
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
    $query_Response = $session->queryFiles(true);
    if(!$query_Response)
    {
        throw new Exception("Error: bad response. Could not get files\n");
    }
    $response_msg = $query_Response->msg;
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage();
}
try
{
    $all_loans_query_response = $session->requestAllLoanIds();
    if(!$all_loans_query_response)
    {
        throw new Exception("Error: could not get all loan ids\n");
    }
    $all_loans_response_msg = $all_loans_query_response->msg;
    $loans = extractLoanIds($all_loans_response_msg);
    $db->insertLoans($loans);
    
    $file_names = extractFileNames($response_msg);
    if(!$file_names)
    {
        throw new Exception("No new files.\n");
    }
    
    foreach($file_names as $file)
    {
        try{
            [$loan_number, $doc_type, $file_name, $formatted_datetime] = extractDataFromFileName($file);
            $session_documents[] = [$loan_number, $doc_type, $file_name, $formatted_datetime];
        }
        catch(Exception $e)
        {
            echo "Error: $file has bad format\n";
            echo currTime()." ".$e->getMessage();
        }
    }
    echo currTime()." inserting documents\n";
    $db->insertDocuments($session_documents);
    //////
    echo currTime()." Checking for missing documents\n";
    updateAllDocuments($session, $db);
    
    /////
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage();
}
try
{    
    $session->endSession();
    $db->endDbConnection();
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage();
}
echo currTime()." Job Finished\n\n##################\n";

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

?>