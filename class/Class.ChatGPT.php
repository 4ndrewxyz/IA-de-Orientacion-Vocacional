<?php
// filepath: /Users/andrew/Downloads/Tesis Project/gpt/chatgpt/class/Class.ChatGPT.php

class ChatGPT {

    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $api_key = ''; // Your API key
    private $model = ''; // Your fine-tuned model ID
    private $streamHandler;
    private $question;
    private $dfa = NULL;
    private $check_sensitive = FALSE;

    public function __construct($params) {
        $this->api_key = $params['api_key'] ?? '';
        
        // Initialize session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize conversation history in session if it doesn't exist
        if (!isset($_SESSION['conversation_history'])) {
            $_SESSION['conversation_history'] = [];
        }
    }

    public function set_dfa(&$dfa){
        $this->dfa = $dfa;
        if(!empty($this->dfa) && $this->dfa->is_available()){
            $this->check_sensitive = TRUE;
        }
    }

    public function qa($params){
        $this->question = $params['question'];
        $this->streamHandler = new StreamHandler([
            'qmd5' => md5($this->question.''.time())
        ]);
        
        // Pass reference to this ChatGPT instance to StreamHandler
        $this->streamHandler->setChatGPT($this);
        
        if($this->check_sensitive){
            $this->streamHandler->set_dfa($this->dfa);
        }

        if(empty($this->api_key)){
            $this->streamHandler->end('Error: API esta vacio');
            return;
        }

        // Start with system message
        $system_message = [
            'role' => 'system',
            'content' => $params['system'] ?? '',
        ];
        
        // Build messages array using conversation history
        $messages = [$system_message];
        
        // Add conversation history (limiting to avoid token limits)
        $max_history_messages = 10; // Adjust based on your needs
        $history_slice = array_slice($_SESSION['conversation_history'], -$max_history_messages);
        $messages = array_merge($messages, $history_slice);
        
        // Add the current user question
        $user_message = [
            'role' => 'user',
            'content' => $this->question
        ];
        $messages[] = $user_message;
        
        // Save the user message to history
        $_SESSION['conversation_history'][] = $user_message;

        $json = json_encode([
            'model' => $this->model,  // usar modelo entrenado aqui
            'messages' => $messages,
            'temperature' => 0.6,
            'stream' => true,
        ]);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$this->api_key,
        );

        $this->openai($json, $headers);
    }

    private function openai($json, $headers){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this->streamHandler, 'callback']);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            file_put_contents('./log/curl.error.log', curl_error($ch).PHP_EOL.PHP_EOL, FILE_APPEND);
        }

        curl_close($ch);
    }
    
    // Method to save assistant responses to conversation history
    public function saveAssistantResponse($response) {
        $_SESSION['conversation_history'][] = [
            'role' => 'assistant',
            'content' => $response
        ];
    }
    
    // Method to clear conversation history (for "New Chat" functionality)
    public function clearConversationHistory() {
        $_SESSION['conversation_history'] = [];
    }
}
?>
