<?php

function extractDataFromFileName($filename)
{
    $regex_pattern = '/(\d+)-([A-Za-z0-9_]+)-(\d{8})_(\d{2}_\d{2}_\d{2})(\.pdf)/';

    if(preg_match($regex_pattern, $filename, $matches))
    {
        
        preg_match('/^([A-Za-z]+_?[A-Za-z]+)(?=[_0-9]?|-)/', $matches[2], $docTypeMatches);
        $loan_number = $matches[1];
        $doc_type = $docTypeMatches[1];
        $date = $matches[3];
        $time = $matches[4];
        $file_ext = $matches[5];

        echo $loan_number."\n";
        echo $doc_type."\n";
        echo $date."\n";
        echo $time."\n";
        echo $file_ext."\n";
    }
}

extractDataFromFileName('304671829-MOU_3-20241017_20_30_59.pdf');

?>