<?php
date_default_timezone_set('America/Mexico_City');
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (@ob_end_flush()) {}
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
require './class/Class.DFA.php';
require './class/Class.StreamHandler.php';
require './class/Class.ChatGPT.php';


echo 'data: '.json_encode(['time'=>date('Y-m-d H:i:s'), 'content'=>'']).PHP_EOL.PHP_EOL;
flush();

$question = urldecode($_GET['q'] ?? '');
if(empty($question)) {
    echo "event: close".PHP_EOL;
    echo "data: Connection closed".PHP_EOL.PHP_EOL;
    flush();
    exit();
}
$question = str_ireplace('{[$add$]}', '+', $question);

if (isset($_GET['action']) && $_GET['action'] === 'new_chat') {
    include_once './class/Class.ChatGPT.php';
    
    $chatgpt = new ChatGPT([
        'api_key' => ''//API Key
    ]);
    $chatgpt->clearConversationHistory();
    
    // Redirect back to the chat interface
    header('Location: index.php');
    exit;
}


//API Key 
$chat = new ChatGPT([
    'api_key' => ''//API Key,
]);


$chat->qa([
	'system' => 'Eres un tutor de orientación vocacional, puedes preguntarme acerca de las carreras y tu futuro y te ayudare a guiarte a cual elegir.',
	'question' => $question,
]);
