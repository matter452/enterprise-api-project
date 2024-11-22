<?php
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/Db.php';
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = basename($_SERVER['PHP_SELF']);
$queryString = $_SERVER['QUERY_STRING'];

switch($method)
{
    case 'GET':
        try
        {
            ob_start();
            $db = new Db(DB_USER, DB_PASS, DB_NAME);
            ob_end_clean();
            if($endpoint === 'documents.php')
            {
                $document_type = isset($_GET['document_doc_select']) ? $_GET['document_doc_select'] : false;
                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : false;
                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : false;
                $loan_number = isset($_GET['loan_number']) ? $_GET['loan_number'] : false;
                ob_start();
                $result = $db->getDocuments($document_type, $start_date, $end_date, $loan_number);
                ob_end_clean();
                header('Content-Type: application/json');
                header('HTTP/1.1 200 OK');
                $response = ['message' => 'Success', 'data' => $result ?: []];
                echo json_encode($response);
            }
            else {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => 'Invalid endpoint.']);
            }
        }
        catch(Exception $e)
        {
            $response = ['error' => $e->getMessage()];
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode($response);
        }
        break;
        default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['error' => 'Method not allowed.']);
        break;
}
exit;
?>