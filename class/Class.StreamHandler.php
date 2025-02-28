<?php

class StreamHandler {

    private $data_buffer;
    private $counter;
    private $qmd5;
    private $chars;
    private $punctuation;
    private $dfa = NULL;
    private $check_sensitive = FALSE;

public function __construct($params) {
    $this->data_buffer = ''; // Initialize as a string
    $this->counter = 0;
    $this->qmd5 = $params['qmd5'] ?? time();
    $this->chars = [];
    $this->punctuation = ['，', '。', '；', '？', '！', '……'];
}

    public function set_dfa(&$dfa){
        $this->dfa = $dfa;
        if(!empty($this->dfa) && $this->dfa->is_available()){
            $this->check_sensitive = TRUE;
        }
    }

    public function setChatGPT(&$chatGPT) {
        $this->chatGPT = $chatGPT;
    }

    private $chatGPT = NULL;

    public function callback($ch, $data) {
        $this->counter += 1;
        file_put_contents('./log/data.'.$this->qmd5.'.log', $this->counter.'=='.$data.PHP_EOL.'--------------------'.PHP_EOL, FILE_APPEND);

        $result = json_decode($data, TRUE);
        if(is_array($result)){
        	$this->end('openai Error en request'.json_encode($result));
        	return strlen($data);
        }


        $buffer = $this->data_buffer.$data;
        
        $this->data_buffer = '';

        $buffer = str_replace('data: {', '{', $buffer);
        $buffer = str_replace('data: [', '[', $buffer);

        $buffer = str_replace("}\n\n{", '}[br]{', $buffer);
        $buffer = str_replace("}\n\n[", '}[br][', $buffer);

        $lines = explode('[br]', $buffer);

        
        $line_c = count($lines);
        foreach($lines as $li=>$line){
            if(trim($line) == '[DONE]'){
                
                $this->data_buffer = '';
                $this->counter = 0;
                $this->sensitive_check();
                $this->end();
                break;
            }
            $line_data = json_decode(trim($line), TRUE);
            if( !is_array($line_data) || !isset($line_data['choices']) || !isset($line_data['choices'][0]) ){
                if($li == ($line_c - 1)){
                    
                    $this->data_buffer = $line;
                    break;
                }
                
                file_put_contents('./log/error.'.$this->qmd5.'.log', json_encode(['i'=>$this->counter, 'line'=>$line, 'li'=>$li], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL, FILE_APPEND);
                continue;
            }

            if( isset($line_data['choices'][0]['delta']) && isset($line_data['choices'][0]['delta']['content']) ){
            	$this->sensitive_check($line_data['choices'][0]['delta']['content']);
            }
        }

        return strlen($data);
    }

    private function sensitive_check($content = NULL){
        
        if(!$this->check_sensitive){
            $this->write($content);
            return;
        }
    	
        if(!$this->has_pause($content)){
            $this->chars[] = $content;
            return;
        }
        $this->chars[] = $content;
        $content = implode('', $this->chars);
        if($this->dfa->containsSensitiveWords($content)){
            $content = $this->dfa->replaceWords($content);
            $this->write($content);
        }else{
            foreach($this->chars as $char){
                $this->write($char);
            }
        }
        $this->chars = [];
    }

    private function has_pause($content){
        if($content == NULL){
            return TRUE;
        }
        $has_p = false;
        if(is_numeric(strripos(json_encode($content), '\n'))){
            $has_p = true;
        }else{
            foreach($this->punctuation as $p){
                if( is_numeric(strripos($content, $p)) ){
                    $has_p = true;
                    break;
                }
            }
        }
        return $has_p;
    }

    private function write($content = NULL, $flush=TRUE){
        if($content != NULL){
            echo 'data: '.json_encode(['time'=>date('Y-m-d H:i:s'), 'content'=>$content], JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
        }        

        if($flush){
            flush();
        }
    }

    public function end($content = NULL){
        if(!empty($content)){
            $this->write($content, FALSE);
        }

    	echo 'retry: 86400000'.PHP_EOL;
    	echo 'event: close'.PHP_EOL;
    	echo 'data: Connection closed'.PHP_EOL.PHP_EOL;
    	flush();

    }
}
