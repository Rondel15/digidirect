<?php

namespace Digidirect\Feed\Export\Resolver;

use Digidirect\Feed\Export\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class StoreResolver extends AbstractResolver
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StoreResolver constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param PoolFactory $poolFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        PoolFactory $poolFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $storeManager, $filesystem, $poolFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($value, $key = null)
    {
        if (!$key && $value instanceof Store) {
            return $value->getName();
        }

        return parent::toString($value, $key);
    }

    /**
     * Return store base email
     *
     * @param Store $store
     * @return string
     */
    public function getEmail($store)
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
    }
}
