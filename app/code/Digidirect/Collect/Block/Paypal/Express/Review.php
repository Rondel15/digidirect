<?php
namespace Digidirect\Collect\Block\Paypal\Express;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Review
 *
 * @package Digidirect\Collect\Block\Plugin\Paypal\Express
 */
class Review extends \Magento\Paypal\Block\Express\Review
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $collectHelper;

    /**
     * Review constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        PriceCurrencyInterface $priceCurrency,
        \Digidirect\Collect\Helper\Data $collectHelper,
        array $data = []
    ) {
        parent::__construct($context, $taxHelper, $addressConfig, $priceCurrency, $data);
        $this->collectHelper = $collectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateFile($template = null)
    {
        if (!($templateFileName = parent::getTemplateFile($template))) {
            $templateFileName = parent::getTemplateFile(
                self::extractModuleName(get_parent_class($this)) . '::' . ($template ?: $this->getTemplate())
            );
        }

        return $templateFileName;
    }

    /**
     *  Hide shipping address for collect
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        if ($this->collectHelper->isCollectItems($this->_quote->getId())
            && !$this->collectHelper->isDeliveryItems($this->_quote->getId())) {
            return false;
        }

        return parent::getShippingAddress();
    }
}
