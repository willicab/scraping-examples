<?php
    define('TOKEN', "TELEGRAM_BOT_TOKEN");
    define('HERE_ID', "HERE_ID");
    define('HERE_CODE', "HERE_CODE");

    function sendMethod($method, $params = array()){
        $ch = curl_init("https://api.telegram.org/bot".TOKEN."/$method");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux i686; rv:32.0) Gecko/20100101 Firefox/40.0');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,50);
        curl_setopt($ch,CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        return curl_exec($ch);
    }
