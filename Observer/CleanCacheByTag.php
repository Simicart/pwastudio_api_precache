<?php

namespace Simi\Apiprecached\Observer;

use Magento\Framework\Event\ObserverInterface;

class CleanCacheByTag implements ObserverInterface
{

    private $simiObjectManager;
    private $tagResolver;
    const REGISTRY_RUN_OVER_KEY = 'simi_api_precache_already_run_over';

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $simiObjectManager
    ) {
        $this->simiObjectManager = $simiObjectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {  
        $registry = $this->simiObjectManager->get('\Magento\Framework\Registry');
        if (!$registry->registry(self::REGISTRY_RUN_OVER_KEY)) {
            $this->simiObjectManager->get('Simi\Apiprecached\Helper\Precache')->precache();
            $registry->register(self::REGISTRY_RUN_OVER_KEY, true);
        }
    }

    private function getTagResolver()
    {
        if ($this->tagResolver === null) {
            $this->tagResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\Cache\Tag\Resolver::class);
        }
        return $this->tagResolver;
    }
}
