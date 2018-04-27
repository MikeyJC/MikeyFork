<?php
require_once('include/entryPoint.php');
require_once('modules/AOS_Invoices/AOS_Invoices.php');

global $db;

$date_start = $_REQUEST['date_start'];
$sql = "SELECT id FROM aos_invoices WHERE invoice_date >= '".$date_start."' AND deleted = '0'";
$result = $db->query($sql);
while ($row = $db->fetchByAssoc($result)) {
    $invoice = new AOS_Invoices();
    $invoice->retrieve($row['id']);
    $invoice->save();
}
echo "test";