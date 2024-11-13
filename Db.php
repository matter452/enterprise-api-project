<?php

class Db
{
    private $host = 'localhost';
    private $db_conn;

    function __construct($user, $pass, $db)
    {
        echo currTime()." Establishing Db Connection...\n";
        $db_conn = new mysqli($this->host, $user, $pass, $db);
        if(mysqli_connect_errno())
        {
            return mysqli_connect_error();
        }
        echo "Db connection established\n\n";
        $this->db_conn = $db_conn;
    }

    function endDbConnection()
    {
        echo currTime()." Terminating Db connection...\n";
        $this->db_conn->close();
        echo currTime()." Db connection closed\n\n";
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
            echo currTime()."Attempting to insert Loans...\n";
            $this->createTempLoanTable();
            [$temp_query, $insert_query] = prepareLoansInsertQuery($loan_numbers);

            $statement = $this->db_conn->prepare($temp_query);
            $statement->execute();

            $statement = $this->db_conn->prepare($insert_query);
            
            if($statement === false)
            {
                throw new Exception("Error: could not prepare loans insert statement");
            }
            if($statement->execute())
            {
                $affected_rows = $statement->affected_rows;
                $statement->close();
                echo currTime()." Success: Loans table updated\nAffected Rows: $affected_rows\n\n";
            }
            else
            {
                throw new Exception("Error: Could not save loans data to database.\n");
            }
        }
        catch(Exception $e)
        {
            echo currTime()." ".$e->getMessage();
        }
    }

    function createTempLoanTable()
    {
        $sql_query = "CREATE TEMPORARY TABLE TempLoans (loan_id VARCHAR(50))";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->execute();
        $statement->close();
    }

    function createTempDocTable()
    {
        $sql_query = "CREATE TEMPORARY TABLE TempDocs (file_name VARCHAR(50))";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->execute();
        $statement->close();
    }

    function insertDocuments($documents, $upload_type = "cron", $audit = false)
    {
        $batch_size = 40;

        try
        {
            if($audit)
            {
                $this->createTempDocTable();
                $temp_docs_query = "INSERT INTO `TempDocs` (`file_name`) VALUES " . implode(', ', $documents);
                $statement = $this->db_conn->prepare($temp_docs_query);
                $statement->execute();
                $sql_query = "SELECT temp.file_name
                FROM `TempDocs` temp
                LEFT JOIN `Loan_Documents` ld ON temp.file_name = ld.file_name
                WHERE ld.file_name IS NULL";

                $statement->prepare($sql_query);
                $statement->execute();
                $result = $statement->get_result();
                $result_array = $result->fetch_all(MYSQLI_ASSOC);
                echo currTime()." Finished audit operations.\n";
                $statement->close();
                return $result_array;

            }
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
                    $statement = $this->db_conn->prepare($formatted_query);
                    if(!$statement)
                    {
                        throw new Exception("Error: Could not prepare statement\n\n");
                    }
                    $statement->bind_param(str_repeat("sssss", count($place_holders)), ...$doc_data);
                    if($statement->execute())
                    {
                        $affected_rows = $statement->affected_rows;
                        echo currTime()." Success: Documents inserted\nRows affected: $affected_rows\n\n";
                        $place_holders = [];
                        $doc_data = [];
                        $statement->close();
                    }
                    else
                    {
                        throw new Exception("Error: could not save docs to database\n\n");
                    }
                }
            }
        }
        catch(Exception $e)
        {
            echo currTime()." ".$e->getMessage()."\n\n";
        }  
    }

    function selectDocsWithoutFile()
    {
        $sql_query  = "SELECT `doc_id`, `file_name` FROM `Loan_Documents` WHERE `file` IS NULL";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->execute();
        $result_array = $statement->get_result();
        $returned_rows = $result_array->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        if(empty($returned_rows))
        {
            return false;
        }
        return $returned_rows;
        
    }

    function selectDocidByFilename($file_name)
    {
        $sql_query  = "SELECT `doc_id` FROM `Loan_Documents` WHERE `file_name` = ?";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->bind_param('s', $file_name);
        $statement->execute();
        $result_array = $statement->get_result();
        $returned_rows = $result_array->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        if(empty($returned_rows))
        {
            return false;
        }
        return $returned_rows;
        
    }

    function updateDocumentsTableFileFlag($manual = false, $doc_id = null)
    {
        try
        {
            if(!$manual)
            {

                $sql_query = "UPDATE `Loan_Documents` loand
                    JOIN `document_data` docd ON loand.doc_id = docd.doc_id
                    SET loand.file = 1
                    WHERE docd.file_content IS NOT NULL AND docd.file_content != ''";
                    $statement = $this->db_conn->prepare($sql_query);
                }else{
                    $sql_query = "UPDATE `Loan_Documents` SET `file` = 1 WHERE `doc_id` = ?";
                    $statement = $this->db_conn->prepare($sql_query);
                    $statement->bind_param('i', $doc_id);
            }
            $statement->execute();
            $affected_rows = $statement->affected_rows;
            $statement->close();
            if($affected_rows <= 0)
            {
                echo "No docs to update\n\n";
                return;
            }
            echo "successfully marked hasfile in Documents table.\nRows affected: $affected_rows\n\n";
        }catch(Exception $e)
        {
            echo $e->getMessage()."\n";
        }

    }

    function insertDocumentBinary($document_id, $file_binary)
    {
        try{

            $sql_query = "INSERT INTO `document_data` (`doc_id`, `file_content`) VALUES (?, ?)";
            $statement = $this->db_conn->prepare($sql_query);
            $statement->bind_param('is', $document_id, $file_binary);
            $statement->execute();
            $affected_rows = $statement->affected_rows;
            $statement->close();
            if($affected_rows <= 0)
            {
                return false;
            }
            echo "inserted ".strlen($file_binary)." bytes.";
            return $affected_rows;
        }
        catch(Exception $e)
        {
            echo $e->getMessage()."\n\n";
        }
    }

    function setLastSession($sid)
    {
        try
        {
            $sql_query = "INSERT INTO `Sessions` (`session_id`, `session_number`) VALUES (1, ?)
            ON DUPLICATE KEY UPDATE session_number = ?";

            $statement = $this->db_conn->prepare($sql_query);
            $statement->bind_param('ss', $sid, $sid);
            if($statement->execute())
            {
                echo "Last session stored in database successfully\n\n";
                $statement->close();
            }
            else
            {
                echo "Failed to store session in databse\n\n";
            }
    }
    catch(Exception $e)
    {
        echo $e->getMessage()."\n";
    }
    }

    function getLastSession()
    {
        $sql_query = "SELECT `session_number` FROM `Sessions`";
        $result = $this->db_conn->execute_query($sql_query);
        if($result->num_rows === 0)
        {
            echo "Failed to get session in databse\n\n";
            return false;
        }
        else
        {
            echo "retrieved last session\n\n";
            $sid_result = $result->fetch_row();
            return $sid_result[0];
        }
        
    }

    function getDocWithName($file_name)
    {
        try
        {
            $sql_query = "SELECT `file_name` FROM `Loan_Documents` WHERE `file_name` = ?";
            $statement = $this->db_conn->prepare($sql_query);
            $statement->bind_param('s', $file_name);
            $statement->execute();
            $statement->store_result();
            
            return $statement->num_rows;
        }
        catch(Exception $e)
        {
            echo $e->getMessage()."\n";
        }
    }

    function getCountOfDocTypeForLoan($loan_number, $doc_type)
    {
        try
        {
        $sql_query = "SELECT COUNT(*) AS doc_type_count FROM `Loan_Documents`
        WHERE doc_loan_number = ? AND doc_type = ?";
        $statement = $this->db_conn->prepare($sql_query);
        $statement->bind_param('ss', $loan_number, $doc_type);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_array(MYSQLI_NUM);
        return $row[0];
    }
    catch(Exception $e)
    {
        echo $e->getMessage()."\n";
    }
    }
}
