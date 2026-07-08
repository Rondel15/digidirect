<?php
namespace Digidirect\AbstractAttributes\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\StripTagsFactory;

/**
 * Class Data
 * @package Digidirect\AbstractAttributes\Helper
 */
class Data extends AbstractHelper
{
    const CATALOG_FRONTENTD_LIST_PER_PAGE = 'catalog/frontend/list_per_page';
    const META_DESC_MAX_LENGTH = 160;

    /**
     * @var StripTagsFactory
     */
    protected $stripTagsFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param StripTagsFactory $stripTagsFactory
     */
    public function __construct(
        Context $context,
        StripTagsFactory $stripTagsFactory
    ) {
        $this->stripTagsFactory = $stripTagsFactory;
        parent::__construct($context);
    }

    /**
     * @param string $description
     * @return string
     */
    public function prepareMetaDescription($description)
    {
        /** @var \Magento\Framework\Filter\StripTags $stripTagsFilter */
        $stripTagsFilter = $this->stripTagsFactory->create();
        $description = $stripTagsFilter->filter($description);
        return substr($description, 0, self::META_DESC_MAX_LENGTH);
    }

    /**
     * Retrieve default per page values
     * @return string (comma separated)
     */
    public function getLimitPerPage()
    {
        return $this->scopeConfig->getValue(
            self::CATALOG_FRONTENTD_LIST_PER_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
