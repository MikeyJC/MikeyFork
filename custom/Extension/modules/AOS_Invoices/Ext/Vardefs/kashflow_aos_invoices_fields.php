<?php
$dictionary['AOS_Invoices']['fields']['amount_paid'] = array(
    'name' => 'amount_paid',
    'vname' => 'LBL_AMOUNT_PAID',
    'type' => 'decimal',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
    'readonly' => true
);

$dictionary['AOS_Invoices']['fields']['kashflow_id'] = array(
    'name' => 'kashflow_id',
    'vname' => 'LBL_KASHFLOW_ID',
    'type' => 'id',
    'len' => 18,
    'reportable' => false,
    'source' => 'db',
    'studio' => 'visible',
    'readonly' => true
);

$dictionary['AOS_Invoices']['fields']['from_kashflow'] = array(
    'name' => 'from_kashflow',
    'vname' => 'LBL_FROM_KASHFLOW',
    'type' => 'bool',
    'default' => false,
    'reportable' => false,
    'source' => 'non-db',
);