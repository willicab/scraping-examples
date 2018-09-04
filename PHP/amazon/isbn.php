<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

$startDate = time();

/*
# Get ISBN list
$url = "http://www.topshelfcomix.com/catalog/isbn-list";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
curl_setopt($ch,CURLOPT_TIMEOUT,300);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$str = curl_exec($ch);
curl_close ($ch);

$re = '/isbn-number">([0-9\-]*)/m';
preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

$list = "";
$break = "";
foreach($matches as $k=>$v){
    $list .= $break.$v[1];
    $break = "\n";
}
file_put_contents('isbn-list.txt', $list);
*/

$list = explode("\n", file_get_contents('isbn-list.txt'));
$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'ISBN');
$sheet->setCellValue('B1', 'Name');
$sheet->setCellValue('C1', 'Price');
$index = 2;
foreach($list as $k=>$v){
    print "$k {$v} - ".count($list)."\n";
    if(trim($v != '')) {
        $url = "https://www.amazon.com/gp/search/ref=sr_adv_b/?search-alias=stripbooks&unfiltered=1&field-keywords=&field-author=&field-title=&field-isbn={$v}&field-publisher=&node=&field-p_n_condition-type=&p_n_feature_browse-bin=&field-age_range=&field-language=&field-dateop=During&field-datemod=&field-dateyear=&sort=relevanceexprank&Adv-Srch-Books-Submit.x=0&Adv-Srch-Books-Submit.y=0";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "Cache-Control: no-cache",
            ],
        ]);
        $str = curl_exec($ch);
        curl_close ($ch);
        
        $re = '/<h2 data[^>]*>([^<]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        $name = trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '', ENT_QUOTES | ENT_XML1, 'UTF-8'));
        
        $re = '/a-offscreen\">([^<]*)/m';
        preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
        if(count($m) == 0){
            $re = '/a-size-base a-color-base">([^<]*)/m';
            preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
            $price = trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '', ENT_QUOTES | ENT_XML1, 'UTF-8'));
        } else {
            $price = trim(html_entity_decode(count($m) > 0 ? $m[0][1] : '', ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }
        print "$url\n$name\n$price\n";
        
        $sheet->setCellValue('A'.$index, trim(html_entity_decode($v)));
        $sheet->setCellValue('B'.$index, $name);
        $sheet->setCellValue('C'.$index, $price);
        
        $index++;
    }
}
$writer = new Xlsx($spreadsheet);
$writer->save('isbn.xlsx');

$endDate = time();
echo gmdate("H:i:s", (int)abs($startDate - $endDate));
