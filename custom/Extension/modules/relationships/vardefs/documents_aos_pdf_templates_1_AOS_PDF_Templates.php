<?php
// created: 2017-10-31 13:24:03
$dictionary["AOS_PDF_Templates"]["fields"]["documents_aos_pdf_templates_1"] = array (
  'name' => 'documents_aos_pdf_templates_1',
  'type' => 'link',
  'relationship' => 'documents_aos_pdf_templates_1',
  'source' => 'non-db',
  'module' => 'Documents',
  'bean_name' => 'Document',
  'vname' => 'LBL_DOCUMENTS_AOS_PDF_TEMPLATES_1_FROM_DOCUMENTS_TITLE',
  'id_name' => 'documents_aos_pdf_templates_1documents_ida',
);
$dictionary["AOS_PDF_Templates"]["fields"]["documents_aos_pdf_templates_1_name"] = array (
  'name' => 'documents_aos_pdf_templates_1_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DOCUMENTS_AOS_PDF_TEMPLATES_1_FROM_DOCUMENTS_TITLE',
  'save' => true,
  'id_name' => 'documents_aos_pdf_templates_1documents_ida',
  'link' => 'documents_aos_pdf_templates_1',
  'table' => 'documents',
  'module' => 'Documents',
  'rname' => 'document_name',
);
$dictionary["AOS_PDF_Templates"]["fields"]["documents_aos_pdf_templates_1documents_ida"] = array (
  'name' => 'documents_aos_pdf_templates_1documents_ida',
  'type' => 'link',
  'relationship' => 'documents_aos_pdf_templates_1',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DOCUMENTS_AOS_PDF_TEMPLATES_1_FROM_AOS_PDF_TEMPLATES_TITLE',
);
