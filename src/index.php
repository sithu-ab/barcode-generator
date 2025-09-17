<?php
require '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Example barcode data
$data = 'CLS12345';

// Display barcode in browser
$generator = new BarcodeGeneratorPNG();
$barcodePng = $generator->getBarcode($data, $generator::TYPE_CODE_39, 1.0, 40);
$barcodeBase64 = base64_encode($barcodePng);

echo "<h2>Generated Barcode</h2>";
echo "<img src='data:image/png;base64,{$barcodeBase64}' alt='Barcode'>";
echo "<p>Barcode Data: {$data}</p>";

// PDF folder
$pdfFolder = __DIR__ . '/../pdf';
if (!is_dir($pdfFolder)) {
    mkdir($pdfFolder, 0777, true);
}

// Initialize TCPDF
$pdf = new \TCPDF();
$fileName = 'barcode_' . date('YmdHis');

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ACADEMIE DU VIN');
$pdf->SetTitle($fileName);
$pdf->AddPage('L');
$pdf->SetMargins(10, 10, 10);

// Load ipagp.ttf
$fontsFolder = __DIR__ . '/fonts';
$ttfFile = $fontsFolder . '/ipagp.ttf';
if (!file_exists($ttfFile)) {
    die("TTF font not found: $ttfFile");
}

// Convert TTF font to TCPDF format (only needed once)
$fontName = \TCPDF_FONTS::addTTFfont($ttfFile, 'TrueTypeUnicode', '', 96);
$pdf->SetFont($fontName, '', 12, '', false);

// Draw barcode in PDF
$style = [
    'position' => 'S',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => 'C',
    'border' => false,
    'text' => false,
    'fontsize' => 0,
    'stretchtext' => 0,
];

$pdf->Cell(0, 10, "Barcode: $data", 0, 1, 'C');
$pdf->write1DBarcode($data, 'C39', '', '', 100, 30, 0.8, $style, 'C');

// Save PDF in /pdf folder
$pdfFilePath = $pdfFolder . '/' . $fileName . '.pdf';
$pdf->Output($pdfFilePath, 'F');

echo "<p>PDF created in: <b>/pdf/" . basename($pdfFilePath) . "</b></p>";
