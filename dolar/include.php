<?php
    date_default_timezone_set('America/Caracas');
    define('TOKEN', "TELEGRAM_BOT_TOKEN");
	define('CHAT_ID', "TELEGRAM_CHAT_ID");
    define('PATH', "/PATH/OF/SCRIPTS/");
    define('URL', "https://api.telegram.org/bot");

function sendImage($image, $caption) {

    $token = TOKEN;
    $url   = URL."$token/sendPhoto";
    $path   = PATH;
    $chat_id = CHAT_ID; # willicab

    $post_fields = array(
        "chat_id" => $chat_id,
        "photo"   => new CURLFile(realpath($path.$image)),
        "caption" => $caption,
      	"disable_notification" => true,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function deleteMessage($message_id){
    $token = TOKEN;
    $url   = URL."$token/deleteMessage";
    $chat_id = CHAT_ID; # willicab

    $post_fields = array(
        "chat_id" => $chat_id,
        "message_id"   => $message_id,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function get_left($img_width, $font_size, $font_file, $string) {
    //Grab the width of the text box
    $bounding_box_size = imagettfbbox($font_size, 0, $font_file, $string);
    $text_width = $bounding_box_size[2] - $bounding_box_size[0];
        
    //Return the position the text should start
    return ceil(($img_width - $text_width) / 2);
}

function get_top($img_height, $font_size, $font_file, $string) {
    //Grab the width of the text box
    $bounding_box_size = imagettfbbox($font_size, 0, $font_file, $string);
    $text_height = $bounding_box_size[7] - $bounding_box_size[1];
        
    //Return the position the text should start
    return ceil(($img_height - $text_height) / 2);
}
