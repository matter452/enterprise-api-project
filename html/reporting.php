<?php
require_once '/var/www/private/Db.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/configuration.php';
try{

    ob_start();
    $db = new Db(DB_USER, DB_PASS, DB_NAME);
    $procedures = [
        "CALL uniqueLoans()",
        "CALL listOfUniqueLoans()",
        "CALL sizeOfAllDocs()",
        "CALL avgFileSize()",
        "CALL totalDocs()",
        "CALL averageDocsPerLoan()",
        "CALL loanStats()",
        "CALL loansWithMissing()",
        "CALL completeLoans()",
        "CALL receivedZeroDocs()",
        "CALL docTypeCount()"];
    ob_end_clean();
}catch(Exception $e)
{
    echo $e->getMessage();
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
        <div class="flex flex-col items-center ">

            <?php 
    foreach($procedures as $report)
    {
        echo "<table class='border-2 rounded-sm my-2 w-2/3'>";
        $result = $db->reporting($report);
        if(!empty($result))
        {        
            echo "<tr>";
            foreach(array_keys($result[0]) as $col_head)
            {    
                echo "<th class='text-sm font-semibold bg-slate-100'>" . $col_head . "</th>";
            }
            echo "</tr>";
            
            foreach($result as $row)
            {
                echo "<tr class='bg-white odd:bg-slate-200'>";
                foreach($row as $value)
                {
                    echo "<td class='even:pl-2'>" . $value . "</td>";
                }
                echo "</tr>";
            }
        }else{
            if($report == "CALL completeLoans()")
            echo "<tr><th>Complete Loans</th></tr><tr><td>No complete loans</td></tr>";
    }
    echo "</table>";
}

?>
</div>
</body>
</html>