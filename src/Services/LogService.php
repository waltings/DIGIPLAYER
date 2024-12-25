<?php
namespace Services;

class LogService {
    private $logFile;
    
    public function __construct($type = 'general') {
        $this->logFile = __DIR__ . "/../../logs/{$type}.log";
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory() {
        $dir = dirname($this->logFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }
    
    public function error($message) {
        $this->log($message, 'ERROR');
    }
    
    public function info($message) {
        $this->log($message, 'INFO');
    }
    
    public function debug($message) {
        $this->log($message, 'DEBUG');
    }
}
