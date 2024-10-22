<?php
require_once '/var/www/configuration.php'; //uncomment this when pushing onto server
//require_once 'configuration.php';
require_once 'Session.php';
require_once 'Db.php';

$session = new Session();
$session_id;

/* $session->createSessionRequest($session->curl_ch, $session->getCreds());
$session->displayEndpointResponse(); */

//get the last session from my database
//close any previous session
//creat new session
//$session->createSessionRequest();

//request all loan ids
//for every loan not in my database create a new entry in the loan table

//try
//query files to check which files are ready for transfer, store in array
//catch timeout
//try again in 5, 10, 30 min, then sleep

//check if those files already exist in my db tables, store the ones that don't exist in new array
//for every file in that array try to request_file and store it.

//check if all 

$db = new Db(DB_USER, DB_PASS, DB_NAME);
$db->endDbConnection(); 

while(true)
{
    echo "###--choose option: 0 create session, 1 end session, 2 query api, 3 request file, 4 close program--###\n";
    fscanf(STDIN, "%d\n", $option);
    switch ($option)
    {
    case 0:
        $session->createSessionRequest();
        $session->setSessionId();
        $session_id = $session->session_id;
        echo "\n".$session->request_response->status;
        echo "\n".$session->request_response->msg;
        echo "\n".$session->request_response->action."\n";
        break;
    case 1:
        $session->endSession();
        echo "\n".$session->request_response->status;
        echo "\n".$session->request_response->msg;
        echo "\n".$session->request_response->action."\n";
        break;
    case 2:
        echo "\nenter endpoint and url parameters: endpoint data\n";
        $input = trim(fgets(STDIN));
        $words = preg_split('/\s+/', $input);
        $endpoint = $words[0];
        $data = $words[1];
        $session->query($endpoint, $data);
        echo "\n".$session->request_response->status;
        echo "\n".$session->request_response->msg;
        echo "\n".$session->request_response->action."\n";
        break;
    case 3:
        echo "\nenter file id:\n";
        $input = trim(fgets(STDIN));
        $session->requestFileQuery($input);
        echo "\n".$session->request_response->status;
        echo "\n".$session->request_response->action."\n";
        echo "\n".$session->request_response->msg."\n";
        break;
    case 4:
        $session->endSession();
        exit();
    }

}


?>