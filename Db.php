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

    function insertDocuments($documents, $upload_type = "cron")
    {
        $batch_size = 40;
        try
        {
            $db = $this->db_conn;

            $sql_query = "INSERT INTO `Loan_Documents` 
            (`doc_loan_number`, `doc_type`, `file_name`, `upload_datetime`, `upload_type`)
            VALUES ";
            $place_holders = [];
            $doc_data = [];
            $current_doc_number = 0;

            foreach($documents as $doc)
            {
                $current_doc_number++;
                [$loan_number, $doc_type, $file_name, $formatted_datetime] = $doc;

                array_push($doc_data, $loan_number, $doc_type, $file_name, $formatted_datetime, $upload_type);
                $place_holders[] = "(?, ?, ?, ?, ?)";

                if($current_doc_number % $batch_size == 0 || $current_doc_number == count($documents))
                {
                    $formatted_query = $sql_query.implode(", ", $place_holders);
                    $statement = $db->prepare($formatted_query);
                    if(!$statement)
                    {
                        throw new Exception("Error: Could not prepare statement");
                    }
                    $statement->bind_param(str_repeat("sssss", count($place_holders)), ...$doc_data);

                    if($statement->execute())
                    {
                        $affected_rows = $statement->affected_rows;
                        echo "Success: Documents inserted\nRows affected: $affected_rows\n";
                    }
                    else
                    {
                        throw new Exception("Error: could not save docs to database\n");
                    }
                    $place_holders = [];
                    $doc_data = [];
                    $statement->close();
                }

            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }  
    }

    function selectDocsWithoutFile()
    {
        $db = $this->db_conn;   
        $sql_query  = "SELECT `doc_id`, `file_name` FROM `Loan_Documents` WHERE `file` IS null";
        $statement = $db->prepare($sql_query);
        $statement->execute();
        $result_array = $statement->get_result();
        if($statement->num_rows <= 0)
        {
            return false;
        }
        $result_data = $result_array->fetch_all(MYSQLI_ASSOC);
        return $result_data;
        
    }

    function insertDocumentBinary($document_id, $file_binary)
    {

        $db = $this->db_conn;
        $sql_query = "INSERT INTO `document_data` (`doc_id`, `file_content`) VALUES (?, ?)";
        $statement = $db->prepare($sql_query);
        $statement->bind_params('sb', $document_id, $file_binary);
        $statement->execute();
        $affected_rows = $statement->affected_rows;
        if($affected_rows <= 0)
        {
            return false;
        }
        return $affected_rows;
    }
}
