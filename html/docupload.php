<?php
require_once '/var/www/private/Db.php';
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';

$temp_dir = "/var/www/private/tempUpload/";
$upload_success = 1;
$pdf_type = "application/pdf";

$response = [
    "success" => null,
    "message" => ""
];

function ValidateUploadForm($allowed_file_type = "application/pdf")
{
    if (!isset($_FILES["userfile"])) {
        throw new Exception("Error: No file selected for upload.");
    }
    $user_file = $_FILES["userfile"]["name"];
    $file_type = $_FILES["userfile"]["type"];
    $file_tmp_path = $_FILES["userfile"]['tmp_name'];
    $loan_option = isset($_POST['loanoption']) ? $_POST['loanoption'] : NULL;
    $loanNum = isset($_POST['loanNum']) ? $_POST['loanNum'] : NULL;
    $doc_type = isset($_POST['docType']) ? $_POST['docType'] : NULL;

    $valid_loan_num = preg_match("/^[0-9]{5,9}$/", trim($loanNum));

    if (is_null($loanNum) || is_null($doc_type) || is_null($loan_option) || is_null($user_file)) {
        throw new Exception("Error: could not upload. Missing Required field(s)");
    }

    if ($file_type != $allowed_file_type) {
        throw new Exception("Error: File type not allowed. File must be pdf");
    }
    if ($valid_loan_num == false){
        
        throw new Exception("Error: Invalid Loan number. Number must be 5-9 digits");
    }
    else
    {
        return [$user_file, $file_type, $file_tmp_path, $loan_option, $loanNum, $doc_type];
    }
}

function CheckIfLoan($loan_number, $db_conn, $new_loan = false)
{       
    ob_start();
    $loan_exists = $db_conn->getLoanNumber($loan_number) > 0 ? true : false;
    ob_end_clean();
    if($new_loan && $loan_exists)
    {
        return false;
    }
    if(!$new_loan && !$loan_exists)
    {
        return false;
    }
    
    return true;    
}

function CreateFileName($loan_num, $doc_type, $db_conn, $new_loan = false)
{
    if(!$new_loan)
    {
        ob_start();
        $doc_type_count = $db_conn->getCountOfDocTypeForLoan(trim($loan_num), trim($doc_type));
        ob_end_clean();
        if ($doc_type_count > 0) {
            if ($doc_type_count == 1) {
                $doc_type_count = 1;
            } 
            else 
            {
                $doc_type_count -= 1;
            }
            $doc_type_formatted = $doc_type . "_" . "$doc_type_count";
        } 
        else 
        {
            $doc_type_formatted = $doc_type;
        }
    }
    if($new_loan)
    {
        $doc_type_formatted = $doc_type;
    }
    $time = date("Ymd_H_i_s");
    $file_name = $loan_num . "-" . $doc_type_formatted . "-" . $time . ".pdf";
    return $file_name;
}

function UploadDocument($file_name, $db_conn, $file_tmp_path)
{
    $file_names = [$file_name];
    ob_start();
    addDocumentsToDb($db_conn, $file_names, 'manual', false);
    ob_end_clean();
    ob_start();
    $doc_id_assoc = $db_conn->selectDocidByFilename($file_name);
    ob_end_clean();
    $doc_id = $doc_id_assoc[0]['doc_id'];
    $fp = fopen($file_tmp_path, "r");
    $contents = fread($fp, filesize($file_tmp_path));
    ob_start();
    $db_conn->insertDocumentBinary($doc_id, $contents);
    $db_conn->updateDocumentsTableFileFlag(true, $doc_id);
    $db_conn->endDbConnection();
    ob_end_clean();
}

if (isset($_POST['submit']) && $_POST['submit'] == "submit") {
    try {
        [$user_file, $file_type, $file_tmp_path, $loan_option, $loan_num, $doc_type] = ValidateUploadForm();
        ob_start();
        $db_conn = new Db(DB_USER, DB_PASS, DB_NAME);
        ob_end_clean();
        
        if ($loan_option == 'new') {
            if(!CheckIfLoan($loan_num, $db_conn, true))
            {
                throw new Exception("Error: Loan number already exists");
            }
            $db_conn->insertLoans([trim($loan_num)]);
            
            $file_name = CreateFileName($loan_num, $doc_type, $db_conn, true);
            UploadDocument($file_name, $db_conn, $file_tmp_path);
            
        }
        
        if ($loan_option == 'existing') {
            if(!CheckIfLoan($loan_num, $db_conn, false))
            {
                throw new Exception("Error: Loan number does not exist");
            } 
            $file_name = CreateFileName($loan_num, $doc_type, $db_conn, false);
            
            UploadDocument($file_name, $db_conn, $file_tmp_path);
        }
        $response["success"] = true;
        $response["message"] = "Successfully uploaded document";
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = $e->getMessage();
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Upload to New Loan</title>
    <!-- BOOTSTRAP STYLES-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>

    <div id="container" class="container">
        <div class="row justify-content-center">
            <h1 class="text-center">Upload a New File to Database</h1>
            <div class="panel-body col-6 align-items-center">
                <div id="formMessage" class="<?= is_null($response["success"]) ? 'd-none' : ''?><?= $response["success"] === false ? 'alert alert-danger' : 'alert alert-success'?>" role="alert">
                    <?= !is_null($response["success"]) ? $response["message"] : '' ?>
                </div>
                <form id="uploadForm" class="container justify-content-center" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
                    <div class="row form-group row-gap-3">
                        <div class="form-check mb-3 px-0">
                            <input type="radio" class="btn-check" name="loanoption" id="existing-outlined" value="existing" autocomplete="off" required>
                            <label class="btn btn-outline-primary" for="existing-outlined">Existing Loan</label>

                            <input type="radio" class="btn-check" name="loanoption" id="new-outlined" value="new" autocomplete="off" required>
                            <label class="btn btn-outline-primary" for="new-outlined">New Loan</label>
                        </div>
                    </div>
                    <div class="row form-group row-gap-3">
                        <label for="loanNum" class="control-label">Loan Number</label>
                        <input type="text" name="loanNum" class="form-control" oninput="validateLoanNum(event)" required>
                        <span id="loanNumMessage">Must be a valid loan number</span>
                    </div>
                    <div class="row form-group row-gap-3">
                        <label for="docType" class="control-label">Document Type</label>
                        <select class="form-control" name="docType" required>
                            <option value="closing">Closing</option>
                            <option value="credit">Credit Report</option>
                            <option value="disclosures">Disclosure</option>
                            <option value="financial">Bank Statement</option>
                            <option value="internal">Internal</option>
                            <option value="legal">Legal</option>
                            <option value="mou">MOU</option>
                            <option value="personal">Personal</option>
                            <option value="preqs">Preq</option>
                            <option value="references">Reference</option>
                            <option value="tax_returns">Tax Document</option>
                            <option value="title">Title</option>
                        </select>
                    </div>
                    <div class="row form-group row-gap-3 justify-content-center">
                        <label class="control-label">File Upload</label>
                        <div class="col col-10">
                            <div class="row btn btn-file text-dark btn-outline-primary hover:bs-primary-border-subtle">
                                <span class="fileupload-new fs-5 fw-medium">Select File</span>
                                <span class="fileupload-exists d-none">Change</span>
                                <input id="fileupload" class="m-3 p-3" name="userfile" type="file" accept="application/pdf" onchange="validateFile()" required>
                            </div>
                            <button href="#" class="btn btn-danger fileupload-exists mt-2" type="button" onclick="removeFile()" data-dismiss="fileupload">Remove</button>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>
                </form>
                <div id="uploadResult">
                    

                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="fileValidation.js"></script>';


    
</body>

</html>