<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

$url = "https://www.consumer.vic.gov.au/housing/renting/types-of-rental-agreements/sharing-in-a-rooming-house/public-register-of-rooming-houses/public-register-of-rooming-houses-full-list?rs=Full&sz=20&ct=15&pg=";
$page = 1;

$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'Local council');
$sheet->setCellValue('B1', 'Full address');
$sheet->setCellValue('C1', 'Business owner name');
$sheet->setCellValue('D1', 'ABN');
$sheet->setCellValue('E1', 'ACN');
$sheet->setCellValue('F1', 'Registration status');
$sheet->setCellValue('G1', 'Council contact');
$index = 2;
do {
    print "Page $page\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.$page);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $str = curl_exec($ch);
    curl_close ($ch);
    
    $re = '/(The search has not found any matching records. Please try a different letter)/';
    preg_match_all($re, $str, $notFound, PREG_SET_ORDER, 0);
    if (count($notFound) > 0) break;

    $re = '/"mobile-toggle"><\/div>\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[\t ]*<a href="mailto:([^\?]*)/m';        
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    foreach($matches as $k=>$v){
        $sheet->setCellValue('A'.$index, trim(html_entity_decode($v[1], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Local council
        $sheet->setCellValue('B'.$index, trim(html_entity_decode(str_replace("<br/>", ", ", $v[2]), ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Full address
        $sheet->setCellValue('C'.$index, trim(html_entity_decode($v[3], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Business owner name
        $sheet->setCellValue('D'.$index, trim(html_entity_decode($v[4], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # ABN
        $sheet->setCellValue('E'.$index, trim(html_entity_decode($v[5], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # ACN
        $sheet->setCellValue('F'.$index, trim(html_entity_decode($v[6], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Registration status
        $sheet->setCellValue('G'.$index, trim(html_entity_decode($v[7], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Council contact
        $index++;
    }
    $page++;
} while (true);
$writer = new Xlsx($spreadsheet);
$writer->save('rooming-houses.xlsx');
