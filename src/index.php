<?php

require '../vendor/autoload.php';

$data = 'CLS' . date('His');
$widthFactor = 2;
$height = 40;

function generateBarcodePNG($data, $widthFactor = 2, $height = 40)
{
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    if (!empty($data)) {
        return base64_encode($generator->getBarcode($data, $generator::TYPE_CODE_39, $widthFactor, $height));
    } else {
        return '';
    }
}

$barcodePNG = generateBarcodePNG($data);

// PDF folder
$pdfFolder = __DIR__ . '/../pdf';
if (!is_dir($pdfFolder)) {
    mkdir($pdfFolder, 0777, true);
}

// Load ipagp.ttf
$fontsFolder = __DIR__ . '/fonts';
$ttfFile = $fontsFolder . '/ipagp.ttf';
if (!file_exists($ttfFile)) {
    die("TTF font not found: $ttfFile");
}

$fileName = 'barcode_' . date('YmdHis');

// Initialize TCPDF
$pdf = new \TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ACADEMIE DU VIN');
$pdf->SetTitle($fileName);
$pdf->AddPage('L');
$pdf->SetMargins(10, 10, 10);

$html = '
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        text-align: center;
    }
    th.price,
    td.price {
        width: 200px
    }
    td.price img {
        width: 180px;
    }
</style>
';
$html .= '<table border="1" cellpadding="5" cellspacing="0">';
$html .= '<thead>';
$html .= '<tr>';
$html .= '  <th>Original</th>';
$html .= '  <th class="price">Width 180</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td>';
$html .= '  <img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" /><br>';
$html .= '  <span>' . $data . '</span>';
$html .= '</td>';
$html .= '<td class="price">';
$html .= '  <img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" /><br>';
$html .= '  <span>' . $data . '</span>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</body>';
$html .= '</table>';
$html .= '<br>';
$html .= '<table border="1" cellpadding="5" cellspacing="0">';
$html .= '<thead>';
$html .= '<tr>';
$html .= '  <th class="price">Width 180</th>';
$html .= '  <th>Original</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td class="price">';
$html .= '  <img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" /><br>';
$html .= '  <span>' . $data . '</span>';
$html .= '</td>';
$html .= '<td>';
$html .= '  <img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" /><br>';
$html .= '  <span>' . $data . '</span>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</body>';
$html .= '</table>';

// Convert TTF font to TCPDF format (only needed once)
$fontName = TCPDF_FONTS::addTTFfont($ttfFile, 'TrueTypeUnicode', '', 96);
$pdf->SetFont($fontName, '', 10, '', false);
// Output the remaining HTML content
$pdf->writeHTML($html, true, false, true, false, '');
ob_clean();

// Display barcode in browser
echo "<h2>Barcode Data: {$data}</h2>";

echo "<h3>Original Barcode</h3>";
$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
echo $generator->getBarcode($data, $generator::TYPE_CODE_39, $widthFactor, $height);

echo "<h3>Original Barcode (img)</h3>";
echo '<img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" />';

echo "<h3>Barcode (img width 180px)</h3>";
echo '<img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" style="width: 180px" />';

// Save PDF in /pdf folder
$pdfFilePath = $pdfFolder . '/' . $fileName . '.pdf';
$pdf->Output($pdfFilePath, 'F');
echo "<p>PDF created in: <b>/pdf/" . basename($pdfFilePath) . "</b></p>";
