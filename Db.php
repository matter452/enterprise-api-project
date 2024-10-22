<?php

class Db
{
    private $host = 'localhost';
    private $db_conn;

    function __construct($user, $pass, $db)
    {
        $db_conn = new mysqli($this->host, $user, $pass, $db);
        if(mysqli_connect_error())
        {
            die("Error connecting to database: ".mysqli_connect_error());
        }
        echo "We connected to db";
        $this->db_conn = $db_conn;
    }

    function endDbConnection()
    {
        $this->db_conn->close();
        echo "connection closed\n";
    }

    function insertDocument($document)
    {
        $db = $this->db_conn;
        $sql_query = "INSERT INTO Loan_Documents (doc_loan_number, doc_type, upload_date, upload_time, file_name, file_content, file_size, upload_type) VALUES (?,?,?,?,?,?,?,?)";
        $statement = $db->prepare($sql_query);

        if($statement == false || NULL)
        {
            die("Error: Failed to prepare statement");
        }

        $statement->bind_param('ssssssss', $loan_number, $doc_type, $upload_date, $upload_time, $file_name, $file_content, $file_size, $upload_type);

        if($statement->execute())
        {
            echo "Document successfully saved.";
        }
        else
        {
            echo "Error saving document.";
        }
        $statement->close();
    }
}