<?php

namespace Simi\Apiprecached\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Precache extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $scopeConfig;
    public $simiObjectManager;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $simiObjectManager,
        \Psr\Log\LoggerInterface $logger,
        DirectoryList $directoryList
    ) {
    
        $this->simiObjectManager = $simiObjectManager;
        $this->scopeConfig = $this->simiObjectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /*
     * Get Store Config Value
     */

    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    public function precache() {
        $newValue = time();
        $lastRunTime = $this->getStoreConfig('apiprecache/general/last_run_time');
        if (!$this->getStoreConfig('apiprecache/general/enable'))
            return;
        //Don't run it every 60 seconds ;)
        if (!$lastRunTime || ((time() - (int)$lastRunTime)) > 60) {
            try {
                $index_path = $this->getStoreConfig('apiprecache/general/index_path');
                $api_var_name = $this->getStoreConfig('apiprecache/general/api_var_name');
                $api_url = $this->getStoreConfig('apiprecache/general/api_url');
                $ctx = stream_context_create(array('http'=>
                    array(
                        'timeout' => 10,  //1200 Seconds is 20 Minutes
                    )
                ));
                $api_result = file_get_contents($api_url, false, $ctx);
                $index_content = file_get_contents($index_path, false, $ctx);
                if ($api_result && $index_content) {
                    $index_content = $this->deleteAllBetween('/*precacheinside*/', '/*endprecacheinside*/', $index_content);
                    $contentToInsert = '';
                    $contentToInsert .= 'var ' . $api_var_name . ' = ' . $api_result . ';';
                    if ($index_content) {
                        $index_content = str_replace('/*precache*//*endprecache*/', '/*precache*//*precacheinside*/' . $contentToInsert .'/*endprecacheinside*//*endprecache*/' , $index_content);
                        file_put_contents($index_path,$index_content);
                    }
                }


    	        $this->simiObjectManager
    	            ->get('Magento\Framework\App\Config\Storage\WriterInterface')
    	            ->save('apiprecache/general/last_run_time', $newValue);
                $this->simiObjectManager
                    ->get('Magento\Framework\App\Cache\TypeListInterface')
                    ->cleanType('config');
    			$this->_logger->notice('Ran over precache Helper at ' . $newValue . ' from ' . $lastRunTime); 
            } catch (\Exception $e) {
                $this->_logger->notice($e->__toString());
            }
        }
    }


    function deleteAllBetween($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }
        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
        return $this->deleteAllBetween($beginning, $end, str_replace($textToDelete, '', $string));
    }
}
