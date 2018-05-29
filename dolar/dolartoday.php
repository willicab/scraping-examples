<?php

    include 'include.php';
    define('FILENAME', './dolartoday.txt');
    define('IMAGE', "./dolartoday.jpg");
    
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, "https://s3.amazonaws.com/south-east-fl/custom/rate.js");
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html = str_replace("var dolartoday = \n", "", curl_exec($ch));
    $input = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($html));
    $json = json_decode($input, true);
    curl_close($ch);
    
    $file = fopen(FILENAME, 'r');
    $salida = fread($file,filesize(FILENAME) + 1);
    fclose($file);
    if ($salida != $json['USD']['transferencia'] && $json['USD']['transferencia'] != '') {
        $file = fopen(FILENAME, 'w');
        fwrite($file, $json['USD']['transferencia']);
        fclose($file);
        
        $im = imagecreatefrompng('dolartoday.png');
        //$negro = imagecolorallocate($im, 0x00, 0x00, 0x00);
        $negro = imagecolorallocate($im, 86, 101, 87);
        $regular = './UbuntuMono-R.ttf';

        $texto = date('d/m/Y h:i', $json['_timestamp']['epoch']);
        $font_size = 14;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 67, $negro, $regular, $texto);

        $texto = 'Transferencia: Bs. '. $json['USD']['transfer_cucuta'];
        $font_size = 24;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 180, $negro, $regular, $texto);

        $texto = 'Efectivo: Bs. '. $json['USD']['efectivo_cucuta'];
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 210, $negro, $regular, $texto);

        $texto = 'Implícito: Bs. '. $json['USD']['efectivo'];
        $font_size = 24;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 240, $negro, $regular, $texto);

        $texto = 'DICOM: Bs. '. $json['USD']['sicad2'];
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 270, $negro, $regular, $texto);

        $texto = 'Transferencia: Bs. '. $json['EUR']['transfer_cucuta'];
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 370, $negro, $regular, $texto);

        $texto = 'Efectivo: Bs. '. $json['EUR']['efectivo_cucuta'];
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 400, $negro, $regular, $texto);

        $texto = 'Implícito: Bs. '. $json['EUR']['efectivo'];
        $font_size = 24;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 430, $negro, $regular, $texto);

        $texto = 'DICOM: Bs. '. $json['EUR']['sicad2'];
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 460, $negro, $regular, $texto);

        header('Content-Type: image/jpg');
        imagejpeg($im, IMAGE);
        imagedestroy($im);
        
        print(sendImage(IMAGE, 'Dolar Today'));
    }
