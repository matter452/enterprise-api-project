<?php

class Loan
{

    public $loan_id;
    public $documents = [];//associative array of documents

    function __construct()
    {

    }

    function addDocument($document)
    {
        $this->documents[] = $document;
    }

    function getDocuments()
    {
        return $documents;
    }

    function getAllDocumentsByLoanId()
    {
        
    }

}

?>