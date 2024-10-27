<?php

class Document
{
    public $loan_id;
    public $file_name;
    public $file_path;
    public $file_binary;
    public $file_type;
    public $upload_date;
    public $upload_time;
    public $download_date;
    public $download_time;
    public $upload_type;

    function __construct(
    $loan_id, $file_name, $file_type, $upload_date, 
    $upload_time, $download_date, $upload_type)
    {
        $this->extractDataFromFileName();
        $this->loan_id = $loan_id;
        $this->file_name = $file_name;
        $this->file_type = $file_type;
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

    function generateDocument($file_binary)
    {
    }

    function removeGeneratedFiles()
    {
        
    }

    function setFileType()
    {
        $this->file_name
    }

    
    
}

?>