<?php
    include 'include.php';
    
    define('CHAT_ID', "TELEGRAM_CHAT_ID");
    define('FILENAME', "sismospe.json");

    # Obtener el código HTML de la página principal de IGP
    $ch = curl_init("http://intranet.igp.gob.pe/bdsismos/ultimosSismosSentidos.php");
    curl_setopt($ch,CURLOPT_REFERER,'http://www.cantv.com.ve');
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,5000);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html = curl_exec ($ch);
    curl_close ($ch);

    # Extraer la data necesaria del código obtenido
	$re = '/content: "<table><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Fecha Local<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Hora Local<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Latitud<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Longitud<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Profundidad<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><tr><td style=\\\\"padding:0px; font-weight:bold;\\\\">Magnitud<\/td><td style=\\\\"padding:0px; \\\\">: ([^\<]*)<\/td><\/tr><\/table>"/m';
	preg_match_all($re, $html, $matches);
	$arrayFecha = $matches[1];
	$arrayHora = $matches[2];
	$arrayLatitud = $matches[3];
	$arrayLongitud = $matches[4];
	$arrayProfundidad = $matches[5];
	$arrayMagnitud = $matches[6];
	$sismos = file_get_contents(FILENAME);
    $obj = json_decode($sismos, true);
	for ($i = 0; $i < count($arrayFecha); $i++) {
        $fecha = $arrayFecha[$i];
        $hora = $arrayHora[$i];
        $latitud = $arrayLatitud[$i];
        $longitud = $arrayLongitud[$i];
        $magnitud = $arrayMagnitud[$i];
        $profundidad = $arrayProfundidad[$i];
        $info = str_replace(':', '', str_replace('/', '', $fecha.$hora));
        if(!in_array($info, $obj)) {
            $obj[] = $info;

            # Crear el texto que se va a enviar
            $text = "$fecha $hora\n";
            $text .= "Magnitud: $magnitud\n";
            $text .= "Profundidad: $profundidad\n";
            $text .= "Ubicación: $latitud, $longitud\n";
            $params = array(
                "chat_id" => CHAT_ID,
                "parse_mode" => "Markdown",
                "photo" => "https://image.maps.cit.api.here.com/mia/1.6/mapview?app_id=".HERE_ID."&app_code=".HERE_CODE."&c=$latitud,$longitud&z=8&u=6m&t=2&w=512&h=512&pip",
                "caption" => $text
            );
            echo "enviando\n";
            print_r(sendMethod("sendPhoto", $params));
        }
	}
    file_put_contents(FILENAME, json_encode($obj));
