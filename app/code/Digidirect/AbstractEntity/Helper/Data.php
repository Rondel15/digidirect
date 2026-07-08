<?php

namespace Digidirect\AbstractEntity\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const POSTFIX_MAX_SIZE = 30;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $attributeSetName
     * @param null $storeId
     * @return string
     */
    public function getIndexTablePostfix(string $attributeSetName, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return substr(
            preg_replace(
                '![^\w\d\s]*!',
                '',
                str_replace(' ', '_', mb_strtolower($attributeSetName))
            ),
            0,
            self::POSTFIX_MAX_SIZE - strlen($storeId) - 1
        ) . '_' . $storeId;
    }
}
