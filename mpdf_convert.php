<?php

require_once('modules/AOS_PDF_Templates/PDF_Lib/mpdf.php');

$mpdf = new mPDF();
$mpdf->tMargin = 0;
$stylesheet = file_get_contents('custom/include/SureVoIP/style.css');
//$pdf->SetImportUse();
//$pageCount = $mpdf->SetSourceFile('custom/include/SureVoIP/SureVoIP_Info-Pack-2017.pdf');
$html = '
<!DOCTYPE html>
<html lang="en">
<header>

</header>
<body>
<div id="page">
    <div id="pg0imglogo">
        <img src="custom/include/SureVoIP/images/header.png">
    </div>
    <div id="title_1">
        <p class="p0 ft2">SUREVOIP</p>
    </div>
    <div id="title_2">
        <p class="p0 ft0">Quote Line Items</p>
    </div>
    <table class="table">
        <tr class="t-header">
            <th class="t-header">Qty</th>
            <th class="t-header" colspan="4">Description</th>
            <th class="t-header">Rate</th>
            <th class="t-header">Total</th>
            <th class="t-header">VAT</th>
        </tr>
        <tr>
            <td class="t-body">3</td>
            <td class="t-body" colspan="4">this is going to be a really long description in order to test if the line wrapping works or if it is super busted.</td>
            <td class="t-body">7.50</td>
            <td class="t-body">22.50</td>
            <td class="t-body">4.50</td>
        </tr>
    </table>
</div>
</body>
<footer>
<div id="footer">
    <table cellpadding=0 cellspacing=0 class="t1">
    <tr>
        <td rowspan=2 class="tr3 td3"><p class="p12 ft18"><span class="ft16">SUREVoIP </span><span class="ft17">| </span>INFORMATION PACK</p></td>
        <td class="tr4 td4"><p class="p12 ft19">&nbsp;</p></td>
        <td rowspan=2 class="tr3 td5"><p class="p14 ft16">Page 1</p></td>
    </tr>
    <tr>
        <td class="tr5 td6"><P class="p12 ft20">&nbsp;</P></td>
    </tr>
    </table>
</div>
</footer>
</html>
';

$mpdf->WriteHTML($stylesheet,1);
$mpdf->WriteHTML($html,2);
$mpdf->Output();