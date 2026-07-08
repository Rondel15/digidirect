<?php

namespace Digidirect\CheckoutFields\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue as CheckoutFieldResource;
use Magento\Sales\Api\Data\OrderInterface;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;

/**
 * Class Data
 * @package Digidirect\CheckoutFields\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Locale date instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var CheckoutFieldResource
     */
    protected $checkoutFieldResource;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * Data constructor.
     * @param Context $context
     * @param TimezoneInterface $localeDate
     * @param CheckoutFieldResource $checkoutFieldResource
     * @param Parser $parser
     */
    public function __construct(
        Context $context,
        TimezoneInterface $localeDate,
        CheckoutFieldResource $checkoutFieldResource,
        Parser $parser
    ) {
        parent::__construct($context);
        $this->localeDate = $localeDate;
        $this->checkoutFieldResource = $checkoutFieldResource;
        $this->parser = $parser;
    }

    /**
     * return date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * @param int|OrderInterface $orderId
     * @param int $fieldId
     * @return mixed|null
     */
    public function getCustomCheckoutOrderFieldValue($orderId, $fieldId)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         * @var $orderFieldValue \Digidirect\CheckoutFields\Model\OrderFieldValue
         * @var $customField \Digidirect\CheckoutFields\Model\OrderFieldValue
         */
        if ($orderId instanceof OrderInterface) {
            $orderId = $orderId->getId();
        }
        return $this->checkoutFieldResource->getCustomCheckoutOrderFieldValue($orderId, $fieldId);
    }

    /**
     * @param int|OrderInterface $orderId
     * @param string $fieldId
     * @param mixed $value
     * @return $this
     * @throws \Exception
     */
    public function saveCustomCheckoutOrderField($orderId, $fieldId, $value)
    {
        if ($orderId instanceof OrderInterface) {
            $orderId = $orderId->getId();
        }

        $checkoutFields = $this->parser->getAllFields();
        if (!isset($checkoutFields[$fieldId])) {
            throw new \Exception(__('Could not find custom checkout field `%1`', $fieldId));
        }

        $this->checkoutFieldResource->saveCustomCheckoutValuesToOrder([
            'code' => $checkoutFields[$fieldId]['frontend_name'],
            'order_id' => $orderId,
            'value' => serialize($value),
            'field_id' => $fieldId,
        ]);

        return $this;
    }
}
