<?php
session_start();
require_once '/var/www/private/Db.php';
require_once '/var/www/private/utils.php';
require_once '/var/www/private/configuration.php';
if(!isset($_SESSION['searchId']))
{
    echo '<p>No active Session... Redirecting in 5 seconds</p>';
    sleep(5);
    header("Location: /dashboard.php");
}
$fid = $_GET['fid'];
ob_start();
$db = new Db(DB_USER, DB_PASS, DB_NAME);
$db->updateAccess($fid);
ob_end_clean();
$db->getDocumentFile($fid);

?>