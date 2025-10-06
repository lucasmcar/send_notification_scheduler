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
        $line = date('Y-m-d H:i:s') . " INFO: " . $msg . ($context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '') . PHP_EOL;
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }

    public function error($msg, $context = []) 
    {
        $line = date('Y-m-d H:i:s') . " ERROR: " . $msg . ($context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '') . PHP_EOL;
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }
}
