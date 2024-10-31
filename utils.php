<?php



function generateFile($document)
{
    $file_write_success = file_put_contents("/var/session_files/$loan_number/".$document->file_name.$document->file_type, $document->file_binary);
    if($file_write_success === false)
    {
        return throw new Exception("Error: Failed to write file ".$document->file_name);
    }
}

function extractFileNames($responseMsg)
{
    $regex_pattern = '/\d+-[A-Za-z0-9_]+-\d{8}_\d{2}_\d{2}_\d{2}\.pdf/';
    if(preg_match_all($regex_pattern, $responseMsg, $matches))
    {
        return $matches[0];
    }
    return false;
}

function extractLoanIds($responseMsg)
{
    $regex_pattern = '/\d+/';
    if(preg_match_all($regex_pattern, $responseMsg, $matches))
    {
        return $matches[0];
    }
    return false;
}

function extractDataFromFileName($file_name)
{
    $regex_pattern = '/(\d+)-([A-Za-z0-9_]+)-(\d{8})_(\d{2}_\d{2}_\d{2})(\.pdf)/';

    if(preg_match($regex_pattern, $file_name, $matches))
    {
        
        preg_match('/^([A-Za-z]+_?[A-Za-z]+)(?=[_0-9]?|-)/', $matches[2], $docTypeMatches);
        $loan_number = $matches[1];
        $doc_type = strtolower($docTypeMatches[1]);
        $date = $matches[3];
        $formatted_date = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $date);
        $time = $matches[4];
        $formatted_time = preg_replace('/(\d{2})_(\d{2})_(\d{2})/', '$1:$2:$3', $time);
        $formatted_datetime = "$formatted_date $formatted_time";
        $file_ext = $matches[5];
    
    echo "lid: $loan_number\n";
    echo "doc type: $doc_type\n";
    echo "date: $formatted_date\n";
    echo "time: $formatted_time\n";
    echo "file ext: $file_ext\n";

    $formatted_strings = "('$loan_number', '$doc_type', '$file_name', '$formatted_datetime')";
    
    return [$loan_number, $doc_type, $file_name, $formatted_datetime];
    }
    return false;
}

function prepareLoansInsertQuery($loan_numbers, $temp_table = true)
{
    if(empty($loan_numbers))
    {
        return [null, null];
    }

    $loans = [];
    
    foreach($loan_numbers as $loan)
    {
        echo "$loan\n";
        $loans[] = "('$loan')";
        
    }

    if($temp_table === true)
    {
        $temp_query = "INSERT INTO `TempLoans` (`loan_id`) VALUES " . implode(', ', $loans);
    }
    
        $insert_query = "INSERT INTO `Loans` (loan_number) 
        SELECT t.loan_id 
        FROM `TempLoans` t
        LEFT JOIN Loans l ON t.loan_id = l.loan_number 
        WHERE l.loan_number IS NULL";
    
    return [$temp_query, $insert_query];
}

/* function prepareDocsInsertQuery($documents, $upload_type = "cron")
{
    $sql_query = "INSERT INTO `Loan_documents` 
    (`doc_loan_number`, `doc_type`, `file_size`, `file_name`, `upload_datetime`, `file_content`, `upload_type`)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $formattedDocs = [];
    foreach($documents as $doc)
    {
        [$loan_number, $doc_type, $file_name, $file_size, $binary_data, $formatted_datetime] = $doc;
        $formattedDocs[] = "('$loan_number', '$doc_type', '$file_size', '$file_name', '$upload_datetime', '$file_content')";
        
    }
    
    echo "\nPrepared doc insert query:\n $sql_query\n";
    return $sql_query;

} */

function addToFileQueue()
{

}

function removeFilesFromQueue()
{

}

function openFileQueue()
{

}

function createFileQueue()
{

}

function closeFileQueue()
{

}

function getFilenameFromQueue()
{

}

function currTime()
{
    $date = date("Y-m-d H:i:s");
    return $date;
}

?>