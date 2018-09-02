<?php

    include 'include.php';
    define('FILENAME', './bolivarcucuta.txt');
    define('IMAGE', "./bolivarcucuta.jpg");

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, "http://bolivarcucuta.com");
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html = curl_exec($ch);
    curl_close($ch);
    
$re = '/<div id="hora">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$hora = $matches[0][1];

$re = '/<div id="dolar">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$dolar = $matches[0][1];

$re = '/<div id="trm">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$trm = $matches[0][1];

$re = '/<div id="bsftc">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$bsftc = $matches[0][1];

$re = '/<div id="bsftv">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$bsftv = $matches[0][1];

$re = '/<div id="bsfec">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$bsfec = $matches[0][1];

$re = '/<div id="bsfev">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$bsfev = $matches[0][1];

$re = '/<div id="usdc">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$usdc = $matches[0][1];

$re = '/<div id="usdv">([^\<]*)/';
preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
$usdv = $matches[0][1];
	
    $file = fopen(FILENAME, 'r');
    $salida = fread($file,filesize(FILENAME) + 1);
    fclose($file);
    if ($salida != $dolar && $dolar != '') {
        $file = fopen(FILENAME, 'w');
        fwrite($file, $dolar);
        fclose($file);
        
        $im = imagecreatefrompng('bolivarcucuta.png');
        //$negro = imagecolorallocate($im, 0x00, 0x00, 0x00);
        $negro = imagecolorallocate($im, 0, 0, 0);
        $regular = './UbuntuMono-R.ttf';

        $texto = $hora;
        $font_size = 14;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 65, $negro, $regular, $texto);

        $texto = $dolar;
        $font_size = 36;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 140, $negro, $regular, $texto);

        $texto = $bsfec;
        $font_size = 32;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 5;
        imagefttext($im, $font_size, 0, $left, 210, $negro, $regular, $texto);

        $texto = $bsfev;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 256;
        imagefttext($im, $font_size, 0, $left, 210, $negro, $regular, $texto);

        $texto = $bsftc;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 5;
        imagefttext($im, $font_size, 0, $left, 310, $negro, $regular, $texto);

        $texto = $bsftv;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 256;
        imagefttext($im, $font_size, 0, $left, 310, $negro, $regular, $texto);

        $texto = $usdc;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 5;
        imagefttext($im, $font_size, 0, $left, 410, $negro, $regular, $texto);

        $texto = $usdv;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(251, $font_size, $regular, $texto) + 256;
        imagefttext($im, $font_size, 0, $left, 410, $negro, $regular, $texto);

        $texto = $trm;
        $font_size = 30;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 497, $negro, $regular, $texto);

        header('Content-Type: image/jpg');
        imagejpeg($im, IMAGE, 30);
        imagedestroy($im);
     	
        print(sendImage(IMAGE, 'Bolivar Cucuta'));
    }
