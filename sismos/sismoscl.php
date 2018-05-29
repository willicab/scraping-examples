<?php
    include 'include.php';
    
    define('CHAT_ID', "TELEGRAM_CHAT_ID");
    define('FILENAME', "sismoscl.json");

    # Obtener el código HTML de la página principal de Funvisis
    $ch = curl_init("http://www.sismologia.cl/links/ultimos_sismos.html");
    curl_setopt($ch,CURLOPT_REFERER,'http://www.cantv.com.ve');
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
    curl_setopt($ch,CURLOPT_TIMEOUT,5000);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec ($ch);
    curl_close ($ch);

    # Extraer la data necesaria del código obtenido
    $re = '/<td>([^<]*)<\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><td>[^<]*<\/td><td>([^<]*)</';   
    preg_match_all($re, $data, $matches);
    
    $data = [];
    for($i = 0; $i < count($matches[0]); $i++) {
        $data[] = [
            'code' => preg_replace('/[:\/ ]/', '', $matches[1][$i]),
            'fecha' => $matches[1][$i],
            'latitud' => $matches[2][$i],
            'longitud' => $matches[3][$i],
            'profundidad' => $matches[4][$i],
            'magnitud' => $matches[5][$i],
            'epicentro' => $matches[6][$i],
        ];
    }
    
    $sismos = file_get_contents(FILENAME);
    $obj = json_decode($sismos, true);
    foreach($data as $k=>$v){
        $code = $v['code'];
        $fecha = $v['fecha'];
        $latitud = $v['latitud'];
        $longitud = $v['longitud'];
        $profundidad = $v['profundidad'];
        $magnitud = $v['magnitud'];
        $epicentro = $v['epicentro'];

        if(!in_array($code, $obj) && floatval(explode(" ", $magnitud)[0])>=5) {
            $obj[] = $code;
            
            # Crear el texto que se va a enviar
            $text = "$fecha\n";
            $text .= "Magnitud: $magnitud\n";
            $text .= "Profundidad: $profundidad\n";
            $text .= "Ubicación: $latitud, $longitud\n";
            $text .= "Epicentro: $epicentro";
            
            $params = array(
                "chat_id" => CHAT_ID,
                "parse_mode" => "Markdown",
                "photo" => "https://image.maps.cit.api.here.com/mia/1.6/mapview?app_id=".HERE_ID."&app_code=".HERE_CODE."&c=$latitud,$longitud&z=8&u=6m&t=2&w=512&h=512&pip",
                "caption" => $text
            );
            echo sendMethod("sendPhoto", $params);
        }
    }
    file_put_contents(FILENAME, json_encode($obj));	
