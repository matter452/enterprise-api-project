<?php
require_once '/var/www/private/configuration.php'; 
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';

try
{
    echo currTime()." Starting downloadFiles job\n\n##################\n";
    $session = new Session();
    $session->createSessionRequest();
    echo currTime()." Session id: $session->session_id\n";
    $queuedFiles= [];
    $downloadedFiles= [];
    $errored_Files= [];

    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    $db->updateDocumentsTableFileFlag();
    $queued_Files = $db->selectDocsWithoutFile();

    if(!$queued_Files)
    {
        throw new Exception("All documents' files stored. nothing to do\n");
    }
    foreach($queued_Files as $file)
    {
        try
        {
            $response_doc_binary = $session->requestFileQuery(trim($file['file_name']));
            $bin = $response_doc_binary->msg;
            usleep(500);
            if(!$response_doc_binary)
            {
                throw new Exception("Error: Failed to download ". $file['file_name']."\n");
                continue;
            }
            echo currTime()." Inserting document binary size of: ".strlen($bin)."\n";
            $result = $db->insertDocumentBinary($file['doc_id'], $bin);
            usleep(500);
            if(!$result)
            {
                throw new Exception("Error: Failed to insert ". $file['file_name']." into db");
            }
            echo currTime()." Query ok: Successfully inserted ".$file['file_name']."\n";
        }
        catch(Exception $e)
        {
            echo currTime()." ".$e->getMessage()."\n";
            continue;
        }
    }
    $db->updateDocumentsTableFileFlag();
    usleep(500);
    $db->endDbConnection();
}
catch(Exception $e)
{
    echo currTime()." ".$e->getMessage()."\n";
}
echo "\n Terminating session...\n";
$session->endSession();
echo currTime()." Job Finished\n\n##################\n";
?>