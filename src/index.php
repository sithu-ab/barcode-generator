<?php

// Enable all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<p>Starting barcode generation...</p>";

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
echo "<p>Looking for autoload at: $autoloadPath</p>";

if (!file_exists($autoloadPath)) {
    die("<p>ERROR: Autoload file not found at $autoloadPath</p>");
}

require $autoloadPath;
echo "<p>Autoload successful!</p>";

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

// Display barcode in browser
echo "<h2>Barcode Data: {$data}</h2>";

echo "<h3>Original Barcode</h3>";
$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
echo $generator->getBarcode($data, $generator::TYPE_CODE_39, $widthFactor, $height);

echo "<h3>Original Barcode (img)</h3>";
echo '<img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" />';

echo "<h3>Barcode (img width 180px)</h3>";
echo '<img src="data:image/png;base64,' . $barcodePNG . '" alt="' . $data . '" style="width: 180px" />';

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
$platform = '<p>Generated platform: ' . php_uname() . '</p>';

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
$html .= '</table>' . $platform;

// Convert TTF font to TCPDF format (only needed once)
$fontName = TCPDF_FONTS::addTTFfont($ttfFile, 'TrueTypeUnicode', '', 96);
$pdf->SetFont($fontName, '', 10, '', false);
// Output the remaining HTML content
$pdf->writeHTML($html, true, false, true, false, '');
// Save PDF in /pdf folder
$pdfFilePath = $pdfFolder . '/' . $fileName . '.pdf';
$pdf->Output($pdfFilePath, 'F');
echo "<p>PDF created in: <b>/pdf/" . basename($pdfFilePath) . "</b></p>";

echo $platform;
