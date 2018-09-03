<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

$url = "https://registers.consumer.vic.gov.au/EaSearch/PerformSearch?Name=e&SoundsLike=True&IncludeNonCurrentLicensees=False&NameOrLicenceNumber=Name&PageNumber=";
$page = 1;

$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'Registered name');
$sheet->setCellValue('B1', 'Type');
$sheet->setCellValue('C1', 'Licence number');
$sheet->setCellValue('D1', 'Date licensed');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Suburb');
$sheet->setCellValue('G1', 'Postcode');
$sheet->setCellValue('H1', 'ABN');
$sheet->setCellValue('I1', 'ACN');
$sheet->setCellValue('J1', 'Principal office address');
$sheet->setCellValue('K1', 'Name of the officer in effective control');
$sheet->setCellValue('L1', 'Telephone');
$sheet->setCellValue('M1', 'Internet address');
$sheet->setCellValue('N1', 'Url');
$index = 2;
do {
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
    
    $re = '/(Sorry, no results were found, please refine your search)/';
    preg_match_all($re, $str, $notFound, PREG_SET_ORDER, 0);
    if (count($notFound) > 0) break;
    
    $re = '/There were ([0-9]*) matching/m';
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    $pages = intval($matches[0][1] / 20) + ($matches[0][1] % 20 > 0 ? 1 : 0);
    $results = $matches[0][1];

    print "* Page $page/~$pages\r\n";
    
    $re = '/Name">\r\n[^\r\n]*\r\n[^<]*<b>([^<]*)<\/b>\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n.+?(?=href)href="([^"]*)/m';        
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    foreach($matches as $k=>$v){
        print " - ".($index - 1)."/~$results\r\n";
        $detailUrl = 'https://registers.consumer.vic.gov.au'.html_entity_decode($v[7]);
        $sheet->setCellValue('A'.$index, trim(html_entity_decode($v[1], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Name
        $sheet->setCellValue('B'.$index, trim(html_entity_decode($v[2], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Type
        $sheet->setCellValue('C'.$index, trim(html_entity_decode($v[3], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Licence Number
        $sheet->setCellValue('E'.$index, trim(html_entity_decode($v[6], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Status
        $sheet->setCellValue('F'.$index, trim(html_entity_decode($v[4], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Suburb
        $sheet->setCellValue('G'.$index, trim(html_entity_decode($v[5], ENT_QUOTES | ENT_XML1, 'UTF-8'))); # Postcode
        $sheet->setCellValue('N'.$index, trim(html_entity_decode($detailUrl))); # Url
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $detailUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
        curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
        curl_setopt($ch,CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $str = curl_exec($ch);
        curl_close ($ch);
        
        $re = '/Date licensed\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('D'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $re = '/ABN\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('H'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $re = '/ACN\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('I'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $re = '/Principal office address\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('J'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $re = '/Name of the officer in effective control\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('K'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $re = '/Telephone\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('L'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-')));
        
        $re = '/Internet address\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n[^\r\n]*\r\n[ ]*([^\r\n]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $sheet->setCellValue('M'.$index, trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '-', ENT_QUOTES | ENT_XML1, 'UTF-8')));
        
        $index++;
    }
    $page++;
} while(true);
$writer = new Xlsx($spreadsheet);
$writer->save('consumer-affairs-victoria.xlsx');
