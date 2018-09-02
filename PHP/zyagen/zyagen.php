<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

# Get List of Ref No
$url = "http://zyagen.com/index.php?main_page=advanced_search_result&keyword=a&search_in_description=1&inc_subcat=0&sort=20a&page=";

$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'Catalog #');
$sheet->setCellValue('B1', 'Product Name');
$sheet->setCellValue('C1', 'Size');
$sheet->setCellValue('D1', 'Price');
$sheet->setCellValue('E1', 'Url');
$index = 2;
for ($i = 1; $i <= 107; $i++) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.$i);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $str = curl_exec($ch);
    curl_close ($ch);
    $re = '/prod_model">([^<]*)[^\n]*\n.+?(?=name)[^>]*><a href="([^"]*)">([^<]*)[^\n]*\n.+?(?=url)[^>]*>([^<]*)[^\n]*\n.+?(?=back")[^>]*>([^<]*)/m';
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    print "- $i: ".count($matches)."\n";
    
    foreach($matches as $k=>$v){
        $sheet->setCellValue('A'.$index, html_entity_decode($v[1]));
        $sheet->setCellValue('B'.$index, html_entity_decode($v[3]));
        $sheet->setCellValue('C'.$index, html_entity_decode($v[4]));
        $sheet->setCellValue('D'.$index, html_entity_decode($v[5]));
        $sheet->setCellValue('E'.$index, html_entity_decode($v[2]));
        $index++;
    }
    #if ($i > 10) break;
}
$writer = new Xlsx($spreadsheet);
$writer->save('zyagen.xlsx');
