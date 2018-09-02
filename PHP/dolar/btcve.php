<?php

    include 'include.php';
    define('FILENAME', './btcve.txt');
    define('IMAGE', "./btcve.jpg");

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, "https://api.bitcoinvenezuela.com/");
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html=curl_exec($ch);
    $input = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($html));
    $json = json_decode($input, true);
    curl_close($ch);
    
    $dolar = round($json['BTC']['VEF'] / $json['BTC']['USD'], 2);
    $euro = round($json['BTC']['VEF'] / $json['BTC']['EUR'], 2);

    $file = fopen(FILENAME, 'r');
    $salida = fread($file,filesize(FILENAME) + 1);
    $obj = json_decode($salida, true); 
    fclose($file);
    if ($obj['dolar'] != $dolar) {
       
        $im = imagecreatefrompng('btcve.png');
        //$negro = imagecolorallocate($im, 0x00, 0x00, 0x00);
        $negro = imagecolorallocate($im, 146, 67, 0);
        $regular = './UbuntuMono-R.ttf';

        $texto = date('d/m/Y h:i', $json['time']['timestamp']);
        $font_size = 14;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 65, $negro, $regular, $texto); // Fecha

        $texto = '$1 = Bs. ' . $dolar;
        $font_size = 36;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 150, $negro, $regular, $texto); // Fecha

        $texto = '€1 = Bs. ' . $euro;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 205, $negro, $regular, $texto); // Fecha

        $texto = 'Bs. ' . round($json['BTC']['VEF'], 2);
        $font_size = 30;
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 350, $negro, $regular, $texto); // Fecha

        $texto = '$' . round($json['BTC']['USD'], 2);
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 400, $negro, $regular, $texto); // Fecha

        $texto = '€' . round($json['BTC']['EUR'], 2);
        $font_width = ImageFontWidth($font_size);
        $text_width = $font_width * strlen($texto);
        $left = get_left(512, $font_size, $regular, $texto);
        imagefttext($im, $font_size, 0, $left, 450, $negro, $regular, $texto); // Fecha


        header('Content-Type: image/jpg');
        imagejpeg($im, IMAGE);
        imagedestroy($im);
        
        $input = sendImage(IMAGE, 'Bitcoin Venezuela');
        $json = json_decode($input, true);
        print_r($json);

        print ($json["result"]["date"] - $obj['date'])."\n";
        #if (($json["result"]["date"] - $obj['date']) < 7200) {
            print deleteMessage($obj['id'])."\n";
        #}

        $file = fopen(FILENAME, 'w');
        fwrite($file, json_encode([
            "dolar"=> $dolar, 
            "id"=> $json["result"]["message_id"], 
            "date" => $json["result"]["date"]
        ]));
        fclose($file);

    }
