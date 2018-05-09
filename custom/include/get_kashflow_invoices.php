<?php
require_once('include/entryPoint.php');
require_once('custom/Extension/modules/Schedulers/Ext/ScheduledTasks/KashflowTasks.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

getInvoices();

echo "done";