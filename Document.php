<?php

class Document
{
    public $loan_id;
    public $document_file_name;
    public $document_file_path;
    public $document_type;
    public $upload_date;
    public $upload_time;
    public $download_date;
    public $download_time;
    public $upload_type;

    function __construct(
    $loan_id, $document_file_name, $document_type, $upload_date, 
    $upload_time, $download_date, $upload_type)
    {
        $this->loan_id = $loan_id;
        $this->document_file_name = $document_file_name;
        $this->document_type = $document_type;
        $this->upload_date = $upload_date;
        $this->upload_time = $upload_time;
        $this->download_date = $download_date;
        $this->upload_type = $upload_type;
    }

    function setFilePath()
    {

    }

    function getFilePath()
    {

    }

    function generateDocument()
    {

    }

    function removeGeneratedFiles()
    {
        
    }
    
}

?>