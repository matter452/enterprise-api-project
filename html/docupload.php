<?php

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload to New Loan</title>
    <!-- BOOTSTRAP STYLES-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div id="container" class="container">
        <div class="row justify-content-center">
        <h1 class="text-center">Upload a New File to Database</h1>
        <div class="panel-body col-6 align-items-center">
            <form class="container justify-content-center" method="post" enctype="multipart/form-data" action="/var/www/private/upload.php">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
                <div class="row form-group row-gap-3">
                    <div class="form-check mb-3 px-0">
                        <input type="radio" class="btn-check" name="loanoption" id="existing-outlined" value="existing" autocomplete="off" required>
                        <label class="btn btn-outline-primary" for="existing-outlined">Existing Loan</label>
                    
                        <input type="radio" class="btn-check" name="loanoption" id="new-outlined" value="new"autocomplete="off" required>
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
                                <input id="fileupload" class="m-3 p-3" name="userfile" type="file"  accept="application/pdf, " onchange="validateFile()" required>
                            </div>
                            <button href="#" class="btn btn-danger fileupload-exists mt-2" type="button" onclick="removeFile()" data-dismiss="fileupload">Remove</button>
                        </div>
                </div>
                <hr>
                <button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>
            </form>
        </div>
    </div>
    </div>
    </div>
    <script src="fileValidation.js"></script>
</body>

</html>