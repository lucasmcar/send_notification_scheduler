<?php

class Logger 
{
    private $file;
    
    public function __construct($file) 
    { 
        $this->file = $file; 
    }

    public function info($msg, $context = []) 
    {
        $line = date('c') . " INFO: " . $msg . ($context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '') . PHP_EOL;
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }

    public function error($msg, $context = []) 
    {
        $line = date('c') . " ERROR: " . $msg . ($context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '') . PHP_EOL;
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }
}
