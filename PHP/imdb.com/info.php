<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

$ref = [
    'tt3315342',
    'tt5814060',
    'tt6850820',
    'tt5702446',
    'tt6559390',
];  

$url = "https://www.imdb.com/title/";

$spreadsheet = IOFactory::load("../base.xlsx");
$writer = new Xlsx($spreadsheet);
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Published');
$sheet->setCellValue('C1', 'Genre');
$sheet->setCellValue('D1', 'Rate');
$sheet->setCellValue('E1', 'Description');
$sheet->setCellValue('F1', 'Director');
$sheet->setCellValue('G1', 'Cast');
$sheet->setCellValue('H1', 'Creators');
$sheet->setCellValue('I1', 'Rating');
$index = 2;
foreach($ref as $v){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.$v."/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Host: www.imdb.com",
    ]);
    $str = curl_exec($ch);
    curl_close ($ch);
    
    #print "$url$v\n$str\n";
    
    $re = '/<script type="application\/ld\+json">([^<]*)/m';
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    $json = json_decode($matches[0][1]);
    print "name: {$json->name}\n----\n";
    $coma = "";
    $cast = "";
    foreach($json->actor as $k){
        $cast .= $coma.$k->name;
        $coma = ", ";
    }
    $coma = "";
    $creators = "";
    foreach($json->creator as $k){
        $creators .= isset($k->name) ? $coma.$k->name : '';
        $coma = ", ";
    }
    
    $sheet->setCellValue('A'.$index, isset($json->name) ? $json->name : '');
    $sheet->setCellValue('B'.$index, isset($json->datePublished) ? $json->datePublished : '');
    $sheet->setCellValue('C'.$index, isset($json->genre) ? is_array($json->genre) ? implode(", ", $json->genre) : $json->genre : '');
    $sheet->setCellValue('D'.$index, isset($json->contentRating) ? $json->contentRating : '');
    $sheet->setCellValue('E'.$index, isset($json->description) ? $json->description : '');
    $sheet->setCellValue('F'.$index, isset($json->director->name) ? $json->director->name : '');
    $sheet->setCellValue('G'.$index, $cast);
    $sheet->setCellValue('H'.$index, $creators);
    $sheet->setCellValue('I'.$index, isset($json->aggregateRating->ratingValue) ? $json->aggregateRating->ratingValue : '');

    $index++;
    $writer->save('imdb-info.xlsx');
}
