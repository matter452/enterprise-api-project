<?php
require_once '/var/www/private/Db.php';
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';

$temp_dir = "/var/www/private/tempUpload/";
$upload_success = 1;
$pdf_type = "application/pdf";

$response = [
    "success" => false,
    "message" => "An unknown error occurred."
];

if(isset($_POST['submit']) && $_POST['submit'] == "submit" )
{
    try{
        if (!isset($_FILES["userfile"])) {
            throw new Exception("Error: No file uploaded.");
        }
        $user_file = $_FILES["userfile"]["name"];
        $file_type = $_FILES["userfile"]["type"];
        $file_tmp_path = $_FILES["userfile"]['tmp_name'];
        $loan_option = isset($_POST['loanoption']) ? $_POST['loanoption'] : NULL;
        $loanNum = isset($_POST['loanNum']) ? $_POST['loanNum'] : NULL;
        $docType = isset($_POST['docType']) ? $_POST['docType'] : NULL;

        if(is_null($loanNum) || is_null($docType) || $loan_option || $loanNum)
        {
            throw new Exception("Error: could not upload. Missing Required field(s)");
        }

        if($file_type != $pdf_type){
            throw new Exception("Error: could not upload. File must be pdf");
        }
        
        $db_conn = new Db(DB_USER, DB_PASS, DB_NAME);

        if($loan_option == 'new')
        {
            $file_exists = $db_conn->getDocWithName($user_file) ? true : false;
            if($file_exists)
            {
                throw new Exception("Error: could not upload. File already exists.");
            }
            $db_conn->insertLoans([trim($loanNum)]);
            $doc_type_count = $db_conn->getCountOfDocTypeForLoan(trim($loanNum), trim($doc_type));
            if($doc_type_count > 0)
            {
                if($doc_type_count == 1)
                {
                    $doc_type_count;
                }
                else
                {
                    $doc_type_count -= 1;
                }
                $doc_type_formatted = $docType."_"."$doc_type_count";
            }
            $time = date("Ymd_H_i_s");
            $file_name = `$loanNum-$docTypeFormatted-$time.pdf`;
            $file_names = [`$file_name`];
            addDocumentsToDb($db_conn, $file_names, 'manual', false);
            $doc_id = $db_conn->selectDocidByFilename($file_name);
            $file_bin = file_get_contents($user_file, $file_tmp_path);
            $db_conn->insertDocumentBinary($doc_id, $file_bin);
            $db->endDbConnection();
            $response["success"] = true;
            $response["message"] = "File uploaded successfully!";
        }

        if($loan_option == 'existing')
        {

        }

        header('Content-Type: application/json');
        echo $response;
        
    }catch(Exception $e){
        $response["message"] = $e->getMessage();
        header('Content-Type: application/json');
        echo $response;
    }
}else{

}
?>