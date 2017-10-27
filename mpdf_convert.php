<?php

require_once('modules/AOS_PDF_Templates/PDF_Lib/mpdf.php');

$mpdf = new mPDF();
$mpdf->tMargin = 0;
//$stylesheet = file_get_contents('custom/include/SureVoIP/style.css');
$html = '
<!DOCTYPE html>
<html lang="en">
<body>
<div style="position:relative;" id="page">
    <div id="pg0imglogo">
        <img style="position:absolute;top:0px;left:0px;margin: 0px 0px 0px 0px;padding: 0px;z-index:-1;width:712px;height:131px;" src="custom/include/SureVoIP/images/header.png">
    </div>
    <div id="title_1">
        <p style="border:none;margin: 10px 0px 0px 0px;padding: 0px;;width: 688px;overflow: hidden;font: bold 27px arial, sans-serif;color: #f99d46;line-height: 32px;margin-top: 0px;margin-bottom: 0px;">SUREVOIP</p>
    </div>
    <div id="title_2">
        <p style="border:none;margin: 10px 0px 0px 0px;padding: 0px;;width: 571px;overflow: hidden;font: bold 53px arial, sans-serif;color: #4e8dd2;line-height: 62px;margin-top: 0px;margin-bottom: 0px;">Quote Line Items</p>
    </div>
    <table style="width:90%;alignment:center;border: 8px solid #4e8dd2;border-collapse: collapse">
        <tr style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;">
            <th style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;">Qty</th>
            <th style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;" colspan="4">Description</th>
            <th style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;">Rate</th>
            <th style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;">Total</th>
            <th style="background-color: #4e8dd2;border: 1px solid white;font: bold 12px arial, sans-serif;color: white;">VAT</th>
        </tr>
        <tr>
            <td style="font: 12px arial, sans-serif;border-left: 1px solid #4e8dd2;border-right: 1px solid #4e8dd2;">3</td>
            <td style="font: 12px arial, sans-serif;border-left: 1px solid #4e8dd2;border-right: 1px solid #4e8dd2;" colspan="4">this is going to be a really long description in order to test if the line wrapping works or if it is super busted.</td>
            <td style="font: 12px arial, sans-serif;border-left: 1px solid #4e8dd2;border-right: 1px solid #4e8dd2;">7.50</td>
            <td style="font: 12px arial, sans-serif;border-left: 1px solid #4e8dd2;border-right: 1px solid #4e8dd2;">22.50</td>
            <td style="font: 12px arial, sans-serif;border-left: 1px solid #4e8dd2;border-right: 1px solid #4e8dd2;">4.50</td>
        </tr>
    </table>
</div>
</body>
</html>
';
$htmlFooter = '
<footer>
<div id="footer">
    <table cellpadding=0 cellspacing=0 class="t1">
    <tr>
        <td rowspan=2 style="height: 14px;padding: 0px;margin: 0px;width: 242px;vertical-align: bottom;"><p style="text-align: left;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;font: bold 8px \'Arial\';color: #f99d46;line-height: 10px;"><span style="color: #4e8dd2;">SUREVoIP </span><span style="color: #58235b;">| </span>INFORMATION PACK</p></td>
        <td style="height: 6px;border-bottom: #437ab9 1px solid;padding: 0px;margin: 0px;width: 339px;vertical-align: bottom;"><p style="text-align: left;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;font: 1px \'Arial\';line-height: 6px;">&nbsp;</p></td>
        <td rowspan=2 style="height: 14px;padding: 0px;margin: 0px;vertical-align: bottom;"><p style="text-align: right;padding-left: 35px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;font: bold 8px \'Arial\';color: #4e8dd2;line-height: 10px;">Page 7</p></td>
    </tr>
    </table>
</div>
</footer>
';

$mpdf->SetImportUse();
$pageCount = $mpdf->SetSourceFile('custom/include/SureVoIP/SureVoIP_Info-Pack-2017.pdf');
for($i = 1; $i <= $pageCount; $i++) {
    $mpdf->AddPage();
    $tpl = $mpdf->ImportPage($i);
    $mpdf->UseTemplate($tpl);
}
//$mpdf->WriteHTML($stylesheet,1);
$mpdf->AddPage();
$mpdf->WriteHTML($html,2);
$mpdf->SetHTMLFooter($htmlFooter);
$mpdf->Output();