<?php

$keywords = [
    'pop corn',
    'waves',
];

$max = 900000;
if(!is_dir('gif')) mkdir('gif');
foreach($keywords as $k=>$v){
    if(!is_dir('gif/'.str_replace(' ', '-', $v))) mkdir('gif/'.str_replace(' ', '-', $v));
    for($i = 0; $i <= ($max / 25); $i++) {
        $url = "https://api.giphy.com/v1/gifs/search?api_key=3eFQvabDx69SMoOemSPiYfh9FY0nzO9x&q=".str_replace(" ", "%20", $v)."&limit=25&offset=".($i * 25);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT,TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,50);
        curl_setopt($ch, CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Host: api.giphy.com",
            "Origin: https://giphy.com",
            "Referer: https://giphy.com/search/pop-corn",
        ]);
        $obj = json_decode(curl_exec($ch));
        #$str = curl_exec($ch);
        curl_close ($ch);

        $max = $obj->pagination->total_count;
        
        foreach($obj->data as $m=>$n){
            $id = $n->id;
            #$url = $n->images->original->url;
            $url = $n->images->fixed_height_small->url;
            print "$id.gif\n";
            $filename = "gif/".str_replace(' ', '-', $v)."/$id.gif";
            if(!file_exists($filename)) file_put_contents($filename, fopen($url, 'r'));
        }
        return;
    }
}
