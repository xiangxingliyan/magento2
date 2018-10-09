<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-15
 * Time: 下午 02:52
 */
namespace Ave40\Base\Model;

class Log {
    const BASE_LOG_DIR = 'ave40';
    
    protected $_fileName;
    protected $_logPrefix;
    protected $_statusPrefix = "";
    
    public function __construct($filename=null, $logPrefix='')
    {
        $this->_fileName = $filename ? $filename : '';
        $this->_logPrefix = $logPrefix;
    }
    
    public function setPrefix($prefix='') {
        $this->_logPrefix = $prefix;
        return $this;
    }
    
    public function addInfoRaw($text) {
        $filePath = $this->_getLogFilePath('info');
        $this->_writeFile($filePath, $text);
    }
    
    public function addInfo($text)  {
        $this->addInfoRaw($this->_generateLineLogFormat($text));
    }
    
    public function addInfoUp($text) {
        $this->_statusPrefix = '↑ ';
        $this->addInfo($text);
    }
    
    public function addInfoDown($text) {
        $this->_statusPrefix = '↓ ';
        $this->addInfo($text);
    }
    
    public function addInfoRun($text) {
        $this->_statusPrefix = '- ';
        $this->addInfo($text);
    }
    
    public function addInfoSkip($text) {
        $this->_statusPrefix = '↖ ';
        $this->addInfo($text);
    }
    
    public function addInfoSuccess($text) {
        $this->_statusPrefix = '✔ ';
        $this->addInfo($text);
    }
    
    public function addBothFail($text) {
        $this->_statusPrefix = '✘ ';
        $this->addInfo($text);
        $this->addError($text);
    }
    
    public function addBoth($text) {
        $this->addInfo($text);
        $this->addError($text);
    }
    
    public function addDebugRaw($text) {
        $filePath = $this->_getLogFilePath('debug');
        $this->_writeFile($filePath, $text);
    }
    
    public function addDebug($text)  {
        $this->addDebugRaw($this->_generateLineLogFormat($text));
    }
    
    public function addErrorRaw($text) {
        $filePath = $this->_getLogFilePath('error');
        $this->_writeFile($filePath, $text);
    }
    
    protected function _writeFile($filePath, $text) {
        file_put_contents($filePath, $this->_converObjectToString($text), FILE_APPEND);
        $this->_statusPrefix = '';
        return $this;
    }
    
    public function addError($text)  {
        $this->addErrorRaw($this->_generateLineLogFormat($text));
    }
    
    protected function _getLogFilePath($type='info')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem\DirectoryList $directory */
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $varDir = $directory->getRoot() . '/var/log/';
        $filenmae = $this->_fileName;
        $filePath = "$varDir" . self::BASE_LOG_DIR .'/' .($filenmae?"$filenmae.":''). "$type.log";
        $fileDir = dirname($filePath);
        
        if(!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }
        
        return $filePath;
    }
    
    protected function _generateLineLogFormat($text, $prefix='') {
        $time = date('Y-m-d H:i:s');
        $innerPrefix = $this->_statusPrefix . ($this->_logPrefix ? "[{$this->_logPrefix}] " : "");
        $time = "===== [$time] {$innerPrefix}$prefix";
        return $time . $this->_converObjectToString($text) . "\n";
    }
    
    protected function _converObjectToString($text) {
        if(is_object($text) || is_array($text)) {
            return json_encode($text);
        }
        
        return $text;
    }
}