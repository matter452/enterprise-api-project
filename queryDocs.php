<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';
$session = new Session();
$session_id;
$loans = [];
$session_documents = [];

try
{
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    echo "creating session...\n";
    $session_created = $session->createSessionRequest();
    while(!$session_created)
    {
        $session_created = $session->createSessionRequest();
    }
    $session_id = $session->session_id;
    echo "\nsession created and set\nSID = $session_id\n";
}
catch(Exception $e)
{
    echo $e->getMessage();
    exit();
} 


try
{
    echo "Querying files...\n";
    $query_Response = $session->queryFiles(true);
    if(!$query_Response)
    {
        throw new Exception("Error: bad response. Could not get files\n");
    }
    $response_msg = $query_Response->msg;
}
catch(Exception $e)
{
    echo $e->getMessage();
}
try
{
    echo "\n Requesting all loan ids\n";
    $all_loans_query_response = $session->requestAllLoanIds();
    $all_loans_response_msg = $all_loans_query_response->msg;
    $loans = extractLoanIds($all_loans_response_msg);
    echo "request ok. inserting loans\n";
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
            echo $e->getMessage();
        }
    }
    echo "\ninserting documents\n";
    $db->insertDocuments($session_documents);
}
catch(Exception $e)
{
    echo $e->getMessage();
}
try
{

    echo "Terminating Session.\n";    
    if($session->endSession())
    {
        echo "Success: session closed. goodbye.";
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}


?>