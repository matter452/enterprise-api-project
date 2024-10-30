<?php
require_once '/var/www/private/configuration.php'; 
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Session.php';
require_once '/var/www/private/Db.php';
$session = new Session();
$session->createSessionRequest();
$queuedFiles= [];
$downloadedFiles= [];
$errored_Files= [];
try
{
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    $queued_Files = $db->selectDocsWithoutFile();
    if(!$queued_Files)
    {
        echo "\nAll documents' files stored. nothing to do\n";
        exit();
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}

    foreach($queued_Files as $file)
    {
        try
        {
            echo "requesting ".$file['file_name']."\n";
            $doc_binary = $session->requestFileQuery($file['file_name']);
            if(!$doc_binary)
            {
                throw new Exception("Error: Failed to download ". $file['file_name']);
            }
            echo "inserting document binary\n";
            $result = $db->insertDocumentBinary($file['doc_id'], $doc_binary);
            if(!$result)
            {
                throw new Exception("Error: Failed to insert ". $file['file_name']." into db");
            }
            echo "Query ok: Successfully inserted ".$file['file_name'];
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            continue;
        }
    }

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
$session->endSession();
echo "Session ended";

?>