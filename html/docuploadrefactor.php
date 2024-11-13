<?php
require_once '/var/www/private/Db.php';
require_once '/var/www/private/configuration.php';
require_once '/var/www/private/utils.php';

$response = [
    "success" => false,
    "message" => "An unknown error occurred."
];

function CreateLoan()
{
    
}

function CreateFileName()
{

}

function UploadDocument()
{
    $time = date("Ymd_H_i_s");
    $file_name = $loanNum . "-" . $docTypeFormatted . "-" . $time . ".pdf";
    echo $file_name . "<br>";
    $file_names = [$file_name];
    addDocumentsToDb($db_conn, $file_names, 'manual', false);
    $doc_id_assoc = $db_conn->selectDocidByFilename($file_name);
    $doc_id = $doc_id_assoc[0]['doc_id'];
    $fp = fopen($file_tmp_path, "r");
    $contents = fread($fp, filesize($file_tmp_path));
    $db_conn->insertDocumentBinary($doc_id, $contents);
    $db_conn->updateDocumentsTableFileFlag(true, $doc_id);
    $db_conn->endDbConnection();
}

if (isset($_POST['submit']) && $_POST['submit'] == "submit") {
    try {
        if (!isset($_FILES["userfile"])) {
            throw new Exception("Error: No file uploaded.");
        }
        $user_file = $_FILES["userfile"]["name"];
        $file_type = $_FILES["userfile"]["type"];
        $file_tmp_path = $_FILES["userfile"]['tmp_name'];
        $loan_option = isset($_POST['loanoption']) ? $_POST['loanoption'] : NULL;
        $loanNum = isset($_POST['loanNum']) ? $_POST['loanNum'] : NULL;
        $doc_type = isset($_POST['docType']) ? $_POST['docType'] : NULL;

        if (is_null($loanNum) || is_null($doc_type) || is_null($loan_option) || is_null($user_file)) {
            throw new Exception("Error: could not upload. Missing Required field(s)");
        }

        if ($file_type != $pdf_type) {
            throw new Exception("Error: could not upload. File must be pdf");
        }
        echo "loannum: " . $loanNum . "doctype: " . $doc_type . " new or old loan: " . $loan_option . "filetype: " . $file_type . "<br>";
        $db_conn = new Db(DB_USER, DB_PASS, DB_NAME);

        if ($loan_option == 'new') {
            $file_exists = $db_conn->getDocWithName($user_file) ? true : false;
            if ($file_exists) {
                throw new Exception("Error: could not upload. File already exists.");
            }
            $db_conn->insertLoans([trim($loanNum)]);
            $doc_type_count = $db_conn->getCountOfDocTypeForLoan(trim($loanNum), trim($doc_type));
            if ($doc_type_count > 0) {
                if ($doc_type_count == 1) {
                    $doc_type_count;
                } else {
                    $doc_type_count -= 1;
                }
                $doc_type_formatted = $doc_type . "_" . "$doc_type_count";
            } else {
                $doc_type_formatted = $doc_type;
            }

            $time = date("Ymd_H_i_s");
            $file_name = $loanNum . "-" . $docTypeFormatted . "-" . $time . ".pdf";
            echo $file_name . "<br>";
            $file_names = [$file_name];
            addDocumentsToDb($db_conn, $file_names, 'manual', false);
            $doc_id_assoc = $db_conn->selectDocidByFilename($file_name);
            $doc_id = $doc_id_assoc[0]['doc_id'];
            $fp = fopen($file_tmp_path, "r");
            $contents = fread($fp, filesize($file_tmp_path));
            $db_conn->insertDocumentBinary($doc_id, $contents);
            $db_conn->updateDocumentsTableFileFlag(true, $doc_id);
            $db_conn->endDbConnection();
            $response["success"] = true;
            $response["message"] = "File uploaded successfully!";
        }

        if ($loan_option == 'existing') {
            echo 'existing loan selected<br>';
            $doc_type_count = $db_conn->getCountOfDocTypeForLoan(trim($loanNum), trim($doc_type));
            echo "type count: " . $doc_type_count . "<br>";
            if ($doc_type_count > 0) {
                if ($doc_type_count == 1) {
                    $doc_type_count;
                } else {
                    $doc_type_count -= 1;
                }
                $doc_type_formatted = $doc_type . "_" . $doc_type_count;
                echo "formatted doctype: " . $doc_type_formatted . "<br>";
            } else {
                $doc_type_formatted = $doc_type;
                echo "formatted doctype: " . $doc_type_formatted . "<br>";
            }
            $time = date("Ymd_H_i_s");
            $file_name = $loanNum . "-" . $doc_type_formatted . "-" . $time . ".pdf";
            echo "file_name = " . $file_name . "<br>";
            $file_names = [$file_name];
            addDocumentsToDb($db_conn, $file_names, 'manual', false);
            $doc_id_assoc = $db_conn->selectDocidByFilename($file_name);
            $doc_id = $doc_id_assoc[0]['doc_id'];
            $fp = fopen($file_tmp_path, "r");
            $contents = fread($fp, filesize($file_tmp_path));
            $db_conn->insertDocumentBinary($doc_id, $contents);
            $db_conn->updateDocumentsTableFileFlag(true, $doc_id);
            $db_conn->endDbConnection();
            $response["message"] = "File uploaded successfully!";
        }
        echo $response["message"];
    } catch (Exception $e) {
        $response["message"] = $e->getMessage();
        echo $response["message"];
    }
} else {
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Upload to New Loan</title>
    <!-- BOOTSTRAP STYLES-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body x-data="{ successMessage: <?= json_encode($response['success'] ? $response['message'] : '') ?>, errorMessage: <?= json_encode(!$response['success'] && $response['message'] ? $response['message'] : '') ?> }">

    <div id="container" class="container">
        <div class="row justify-content-center">
            <h1 class="text-center">Upload a New File to Database</h1>
            <div class="panel-body col-6 align-items-center">
                <template x-if="successMessage">
                    <div class="alert alert-success" x-text="successMessage"></div>
                </template>
                <template x-if="errorMessage">
                    <div class="alert alert-danger" x-text="errorMessage"></div>
                </template>
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
                        <input type="text" name="loanNum" class="form-control" oninput="validateLoanNum(event)">
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
                                <input id="fileupload" class="m-3 p-3" name="userfile" type="file" accept="application/pdf, " onchange="validateFile()" required>
                            </div>
                            <button href="#" class="btn btn-danger fileupload-exists mt-2" type="button" onclick="removeFile()" data-dismiss="fileupload">Remove</button>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>
                </form>
                <div id="uploadResult">
                    "$result"

                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="fileValidation.js"></script>';


    $temp_dir = "/var/www/private/tempUpload/";
    $upload_success = 1;
    $pdf_type = "application/pdf";

    ?>
</body>

</html>