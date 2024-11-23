<?php
$_COOKIE['PHPSESSID'] = '';
session_destroy();
session_start();
$_SESSION['searchId'] = session_id();
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body>
    <div class="container mx-auto">
        <div class="flex flex-wrap justify-center">
            <h1 class="text-3xl font-bold underline text-center basis-full">
              Search by
            </h1>
            <form id="search" method="GET" action="" class="flex flex-col basis-1/4">
                <fieldset class="flex flex-row flex-wrap border border-slate-400 my-2 rounded-md p-4 gap-x-2">
                    <p class="font-bold text-center basis-full">Choose a search method</p>
                    <label for="loan_radio">Loan Number</label>
                    <input type="radio" id="loan_radio" class="form-radio" name="search_by" value="loan_radio"/>
                    <label for="loan_radio">Document Type</label>
                    <input type="radio" id="document_radio" class="form-radio" name="search_by" value="document_radio"/>
                </fieldset>
                <fieldset id="loan_section" class="flex flex-col border border-slate-400 my-2 rounded-md p-4">
                    <legend class="font-bold">Loan</legend>
                    <label>Loan number</label>
                    <input id="loan_number" name="loan_number" type="text" class="form-input" pattern="^\d{5,9}$"/>
                    <span class="text-neutral-700 text-sm">Must be a number. Must be between 5-9 digits.</span>
                    <label>Document Type</label>
                    <select id="loan_doc_select" name="document_doc_select" class="form-select">
                        <option value="all">All types</option>
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
                </fieldset>
                <fieldset id="document_section" class="flex flex-col border border-slate-400 my-2 rounded-md p-4">
                    <legend class="font-bold">Document</legend>
                    <label>Document Type</label>
                    <select id="document_doc_select" name="document_doc_select" class="form-select" required>
                        <option value="all">All types</option>
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
                    <label>Start Date</label>
                    <input id="start_date" type="date" name="start_date" class="form-input" />
                    <label>End Date</label>
                    <input id="end_date" type="date" name="end_date" class="form-input" />
                    <span class="text-neutral-700 text-sm">End Date must not be before Start date.</span>
                </fieldset>
                <button id="search_button" type="button" class="bg-sky-700 text-slate-100 text-center rounded-md py-2">Search</button>
            </form>
        </div>

    </div>
    <script src="dashboard.js"></script>
</body>
</html>