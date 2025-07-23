<?php
function sendTelegramMessage($message) {
    // Ganti token dan chat_id sesuai punyamu
    $token = '8144240581:AAF-0aaErL-en0FA9_m40s_bOzlWWLgk_kU';
    $chat_id = '-4795936771';

    $url = "https://api.telegram.org/bot$token/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}
?>
