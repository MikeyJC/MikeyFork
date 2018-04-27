<?php
require_once('include/entryPoint.php');
require_once('modules/AOS_Invoices/AOS_Invoices.php');

$invoice = new AOS_Invoices();
$invoice->retrieve('487f6316-6b0a-860f-5016-5acf2ae82074');
$invoice->save();
echo "test";