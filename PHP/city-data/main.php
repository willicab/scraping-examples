<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

$url = 'http://www.city-data.com/';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
curl_setopt($ch,CURLOPT_TIMEOUT,300);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$html = curl_exec($ch);
curl_close ($ch);

$re = '/<a href="(http:\/\/www\.city-data\.com\/city\/([A-Za-z\-]*\.html))" title="[A-Za-z ]* cities" class="cities_list">([A-Z][^<]{3,})<\/a>/m';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);

# get Info Detail
$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'State');
$sheet->setCellValue('B1', 'City');
$sheet->setCellValue('C1', 'Population');
$sheet->setCellValue('D1', 'Urban Population');
$sheet->setCellValue('E1', 'Rural Population');
$sheet->setCellValue('F1', 'Male Population');
$sheet->setCellValue('G1', 'Female Population');
$sheet->setCellValue('H1', 'City Median Age');
$sheet->setCellValue('I1', 'State Median Age');
$csv = "State;City;Population;Urban Population;Rural Population;Male Population;Female Population;City Median Age;State Median Age";
$index = 2;
foreach($matches as $k=>$v) {
    print "- ".($k + 1)."/".count($matches)." {$v[3]}\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $v[1]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html = curl_exec($ch);
    curl_close ($ch);
    
    $re = '/<td onclick=\'c\([0-9]*\);\'><p><\/p><\/td><td>[<b>]*<a href=\'([^\']*)\'>([^<]*)/m';
    preg_match_all($re, $html, $m, PREG_SET_ORDER, 0);
    
    foreach($m as $i=>$j){
        if (strpos($j[1], 'javascript') !== false) {
            $re = '/javascript:l\("([A-Za-z\-]*)"\);/m';
            preg_match_all($re, $j[1], $u, PREG_SET_ORDER, 0);
            $url = 'http://www.city-data.com/city/' . $u[0][1] . "-" . $v[2];
        } else {
            $url = 'http://www.city-data.com/city/' . $j[1];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
        curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
        curl_setopt($ch,CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $html = curl_exec($ch);
        curl_close ($ch);
        
        print "  - ".($i + 1)."/".count($m)." {$j[2]}\n";
        $sheet->setCellValue('A'.$index, $v[3]);
        $sheet->setCellValue('B'.$index, $j[2]);
        $csv .= "\n{$v[3]};{$j[2]};";

        $re = '/Population"><b>[^<]*<\/b> ([0-9,]*)/m';
        preg_match_all($re, $html, $u, PREG_SET_ORDER, 0);
        $population = $u[0][1];
        $sheet->setCellValue('C'.$index, (count($u) > 0 ? $u[0][1] : '-'));
        $csv .= (count($u) > 0 ? $u[0][1] : '-').";";
        
        $re = '/([0-9\,\.]*)% urban/m';
        preg_match_all($re, $html, $u, PREG_SET_ORDER, 0);
        $sheet->setCellValue('D'.$index, (count($u) > 0 ? $u[0][1]."%" : '-'));
        $csv .= (count($u) > 0 ? $u[0][1]."%;" : '-').";";
        
        $re = '/([0-9\,\.]*)% rural/m';
        preg_match_all($re, $html, $u, PREG_SET_ORDER, 0);
        $sheet->setCellValue('E'.$index, (count($u) > 0 ? $u[0][1]."%" : '-'));
        $csv .= (count($u) > 0 ? $u[0][1]."%" : '-').";";

        $re = '/population-by-sex"><div><table><tr><td><b>Males:<\/b> ([^&]*)&nbsp;<\/td><td><img src="[^"]*" width="[0-9]*" height="[0-9]*">&nbsp;\(([^%]*)%\)<\/td><\/tr><tr><td><b>Females:<\/b> ([^&]*)&nbsp;<\/td><td><img src="[^"]*" width="[0-9]*" height="[0-9]*">&nbsp;\(([^%]*)%/m';
        preg_match_all($re, $html, $u, PREG_SET_ORDER, 0);
        $sheet->setCellValue('F'.$index, (count($u) > 0 ? $u[0][1]." (".$u[0][2]."%)" : '-'));
        $sheet->setCellValue('G'.$index, (count($u) > 0 ? $u[0][3]." (".$u[0][4]."%)" : '-'));
        $csv .= (count($u) > 0 ? $u[0][1]." (".$u[0][2]."%)" : '-').";";
        $csv .= (count($u) > 0 ? $u[0][3]." (".$u[0][4]."%)" : '-').";";

        $re = '/age:&nbsp;<\/b><\/td><td><img src="[^"]*" width="[0-9]*" height="[0-9]*">&nbsp;([^ ]*) years<\/td><\/tr><tr><td><b>[A-Za-z ]* median age:&nbsp;<\/b><\/td><td><img src="[^"]*" width="[0-9]*" height="[0-9]*">&nbsp;([^ ]*)/m';
        preg_match_all($re, $html, $u, PREG_SET_ORDER, 0);
        $sheet->setCellValue('H'.$index, (count($u) > 0 ? $u[0][1] : '-'));
        $sheet->setCellValue('I'.$index, (count($u) > 0 ? $u[0][2] : '-'));
        $csv .= (count($u) > 0 ? $u[0][1] : '-').";";
        $csv .= (count($u) > 0 ? $u[0][2] : '-');
        
        $index++;
    }
}
$writer = new Xlsx($spreadsheet);
$writer->save('city-data.xlsx');
file_put_contents('city-data.csv', $csv);
