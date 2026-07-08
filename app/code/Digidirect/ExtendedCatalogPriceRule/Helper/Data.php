<?php
namespace Digidirect\ExtendedCatalogPriceRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Digidirect\ExtendedCatalogPriceRule\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        parent::__construct($context);
        $this->pricingHelper = $pricingHelper;
        $this->localeFormat = $localeFormat;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @param float $amount
     * @return float|string
     */
    public function formatDiscountAmountAsHtml($amount)
    {
        return $this->pricingHelper->currency($amount, true, false);
    }

    /**
     * @param float $amount
     * @return float
     */
    public function formatDiscountAmount($amount)
    {
        return $this->localeFormat->getNumber($amount);
    }

    /**
     * @param string $content
     * @return string
     */
    public function prepareContent($content)
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($content);
        } catch (\Exception $e) {
            $this->_logger->error(__('Problem with Description formatting occurred: %1', $e->getMessage()));
            return $content;
        }
    }
}
