<?php

class Db
{
    private $host = 'localhost';
    private $db_conn;

    function __construct($user, $pass, $db)
    {
        $db_conn = new mysqli($this->host, $user, $pass, $db);
        if(mysqli_connect_errno())
        {
            return mysqli_connect_error();
        }
        echo "We connected to db\n";
        $this->db_conn = $db_conn;
    }

    function endDbConnection()
    {
        $this->db_conn->close();
        echo "Db connection closed\n";
    }

    function selectDocuments()
    {
        try
        {

            $db = $this->db_conn;
            $sql_query = "SELECT file_name FROM `Loan_Documents`";
            $documentsQueryResult = $db->execute_query($sql_query);
            if($documentsQueryResult === false)
            {
                throw new Exception("Error: could not execute query.");
            }
            else
            {
                return $documentsQueryResult;
            }
            
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    function insertLoans($loan_numbers)
    {
        try
        {
            
            $this->createTempLoanTable();
            $db = $this->db_conn;
            [$temp_query, $insert_query] = prepareLoansInsertQuery($loan_numbers);

            $statement = $db->prepare($temp_query);
            $statement->execute();

            $statement = $db->prepare($insert_query);
            
            if($statement === false)
            {
                throw new Exception("Error: could not prepare doc insert statement");
            }
            if($statement->execute())
            {
                $affected_rows = $statement->affected_rows;
                echo "Success: Loans table updated\nAffected Rows: $affected_rows";
            }
            else
            {
                throw new Exception("Error: Could not save loans data to database.");
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    function createTempLoanTable()
    {
        $sql_query = "CREATE TEMPORARY TABLE TempLoans (loan_id VARCHAR(50))";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->execute();
    }

    function insertDocuments($documents)
    {
        try
        {
            $doc_insert_query = prepare_docs_insert_query($documents);
            $db = $this->db_conn;
            $errored_file_names = [];
            $statement = $db->prepare($doc_insert_query);
            
            if($statement === false)
            {
                throw new Exception("Error: could not prepare doc insert statement");
            }
                /* $loan_id = $document->loan_id;
                $file_name = $document->file_name;
                $file_type = $document->file_type;
                $upload_date = $document->upload_date;
                $upload_time = $document->upload_time;
    
                $statement->bind_param('sssss', $loan_id, $file_type, $upload_date, $upload_time, $file_name); */
            if($statement->execute())
            {
                echo "Success: $documents added to database.";
            }
            else
            {
                throw new Exception("Error: Could not save documents data to database.");
            }
            $statement->close();

        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }  
    }

    function updateDocumentFileBinary()
    {

    }
}