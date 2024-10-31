<?php
require_once '/var/www/private/configuration.php'; 
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';
$session = new Session();
echo "creteing session\n";
try
{
$session->createSessionRequest();
echo "$session->session_id\n";
$queuedFiles= [];
$downloadedFiles= [];
$errored_Files= [];
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    $queued_Files = $db->selectDocsWithoutFile();
    //$queued_Files[] = [['file_name: 204976183-Personal-20241030_14_17_55.pdf']]
    if(!$queued_Files)
    {
        throw new Exception("All documents' files stored. nothing to do\n");
        //exit();
    }
    foreach($queued_Files as $file)
    {
        try
        {
            $response_doc_binary = $session->requestFileQuery(trim($file['file_name']));
            $bin = $response_doc_binary->msg;
            if(!$response_doc_binary)
            {
                throw new Exception("Error: Failed to download ". $file['file_name']."\n");
                continue;
            }
            echo "inserting document binary size of: ".strlen($bin)."\n";
            //echo "\n$bin\n";
            $result = $db->insertDocumentBinary($file['doc_id'], $bin);
            if(!$result)
            {
                throw new Exception("Error: Failed to insert ". $file['file_name']." into db");
            }
            echo "Query ok: Successfully inserted ".$file['file_name']."\n";
        }
        catch(Exception $e)
        {
            echo currTime()." ".$e->getMessage()."\n";
            continue;
        }
    }
    $db->updateDocumentsTableFileFlag();
}
catch(Exception $e)
{
    echo $e->getMessage()."\n";
}
echo "\n Terminating session...";
$session->endSession();
echo "Session ended";

/* $fp = fopen("downloadqueue.txt", "r");
echo "\nFile opened\nReading from file and queing docs\n";
while(!feof($fp))
{
    $line_file_name = trim(fgets($fp));
    if(!empty($line_file_name))
    {
        echo "$line_file_name\n";
        $queuedFiles[] = $line_file_name;
    }
    echo "All Files queued\n";
}
fclose($fp);
echo "file closed\n\n";
foreach($queuedFiles as $file)
{
    try{
       $response = $session->requestFileQuery($file);
        
        echo "\nrequest ok\n";
        [$loan_number, $doc_type, $file_name, $formatted_datetime] = extractDataFromFileName($file);
        $file_size = strlen($response->msg);
        $downloadedFiles[] = [$loan_number, $doc_type, $file_name, $file_size, $response->msg, $formatted_datetime];
    }
    catch(Exception $e)
    {
            echo "Error: $file failed download\n";
            $errored_Files[] = $file;
            echo $e->getMessage();
    }
}

$fp = fopen("downloadqueue.txt", "w");
if(empty($errored_Files))
{
    echo "No file errors. File Queue empty";
    try
    {
        $db = new Db(DB_USER, DB_PASS, DB_NAME);
        $db->insertDocuments($downloadedFiles);
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
}
else
{
    echo "writing errored files back to queue\n";
    foreach($errored_Files as $errored_file)
    {
        fwrite($fp, "$errored_file\n");
    }
    echo "finished writing\n";
}
echo "closing file\n";
fclose($fp);
echo "file closed\n"; */


?>