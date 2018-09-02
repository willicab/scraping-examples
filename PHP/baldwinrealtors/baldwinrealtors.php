<?php
require '../vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

# Get List of Ref No
$url = "http://www.baldwinrealtors.org/index.php?src=directory&view=ActiveAgent&submenu=FindaRealtor&srctype=ActiveAgent_lister_realtor&query=category.ne.Affiliate.and.category.ne.Primary%20Appraiser.and.category.ne.MLS%20Only%20Appraiser.and.category.ne.Secondary%20Appraiser&pos=0,2533,2533";
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
$re = '/refno=([0-9]*)">/m';
preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

# get Info Detail
$spreadsheet = IOFactory::load("../base.xlsx");
$sheet = $spreadsheet->getSheet(0);
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Phone');
$sheet->setCellValue('C1', 'Mobile');
$sheet->setCellValue('D1', 'Email');
$sheet->setCellValue('E1', 'Location');
$sheet->setCellValue('F1', 'Agency');
$csv = "Name;Phone;Mobile;Email;Location;Agency";
$index = 2;
foreach($matches as $k=> $v) {
    $url = "http://www.baldwinrealtors.org/index.php?src=directory&view=ActiveAgent&submenu=FindaRealtor&query=category.ne.Affiliate.and.category.ne.Primary%20Appraiser.and.category.ne.MLS%20Only%20Appraiser.and.category.ne.Secondary%20Appraiser&srctype=detail&back=ActiveAgent&refno=";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.$v[1]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $str = curl_exec($ch);
    curl_close ($ch);
    
    # Name
    $re = '/<h2>([^<]*)<\/h2>/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('A'.$index, (count($m) > 0 ? $m[0][1] : ''));
    $csv .= "\n".(count($m) > 0 ? $m[0][1] : '').";";
    
    # Phone
    $re = '/Phone:<\/td><td><span class="phone">([^<]*)/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('B'.$index, html_entity_decode(count($m) > 0 ? $m[0][1] : ''));
    $csv .= html_entity_decode(count($m) > 0 ? $m[0][1] : '').";";

    # Mobile
    $re = '/Mobile:<\/td><td><span class="phone">([^<]*)/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('C'.$index, html_entity_decode(count($m) > 0 ? $m[0][1] : ''));
    $csv .= html_entity_decode(count($m) > 0 ? $m[0][1] : '').";";
    
    # Email
    $re = '/javascript">document\.write\( \'([^\']*)\' \+ \'@\' \+ \'([^\']*)\' \+ \'\.\' \+ \'([^\']*)\'/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('D'.$index, html_entity_decode(count($m) > 0 ? $m[0][1]."@".$m[0][2].".".$m[0][3] : ''));
    $csv .= html_entity_decode(count($m) > 0 ? $m[0][1]."@".$m[0][2].".".$m[0][3] : '').";";
    
    # Location
    $re = '/Location:<\/td><td>([^<]*)/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('E'.$index, html_entity_decode(count($m) > 0 ? $m[0][1] : ''));
    $csv .= html_entity_decode(count($m) > 0 ? $m[0][1] : '').";";
    
    # Agency
    $re = '/Agency:<\/td><td><a href="[^"]*">([^<]*)/m';
    preg_match_all($re, $str, $m, PREG_SET_ORDER, 0);
    $sheet->setCellValue('F'.$index, html_entity_decode(count($m) > 0 ? $m[0][1] : ''));
    $csv .= html_entity_decode(count($m) > 0 ? $m[0][1] : '');

    $index++;
}
# Save  Excel File
$writer = new Xlsx($spreadsheet);
$writer->save('baldwinrealtors.xlsx');
# Save CSV File
file_put_contents('baldwinrealtors.csv', $csv);

