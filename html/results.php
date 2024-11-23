<?php
//if someone visits the search results page without making a search boot thme back to dashboard to make a search
session_start();
if(!isset($_SESSION['searchId']) || ($_COOKIE['PHPSESSID'] !== session_id()))
{
    echo '<p>Invalid Session... Redirecting in 4 seconds</p>';
    sleep(4);
    header("Location: /dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Table</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="">
    
    <div class="container mx-auto">
        <h1 class="text-3xl font-semibold mb-4">Documents Table</h1>
        <div id="results_table" class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full table-auto">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Doc ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Loan Number</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Doc Type</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">File Name</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">File Size</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Last Access</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Upload Date</th>
                </tr>
            </thead>
            <tbody id="table_body">

                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center">
           
        </div>
    </div>
    
    <script src="results.js"></script>
</body>
</html>
