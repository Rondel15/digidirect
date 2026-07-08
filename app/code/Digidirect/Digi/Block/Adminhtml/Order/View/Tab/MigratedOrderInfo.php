<?php
/**
 * Digidirect
 *
 */
namespace Digidirect\Digi\Block\Adminhtml\Order\View\Tab;

use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Widget\Tab\TabInterface;
use \Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Class MigratedOrderInfo
 * @package Digidirect\Digi\Block\Adminhtml\Order\View\Tab
 */
class MigratedOrderInfo extends Template implements TabInterface
{
    const ADDITIONAL_DATA_TITLE = [
        'Payment type',
        'KW Order Status',
        'Zip Order ID',
        'Brain Tree Type',
        'Cupon code',
        'Pronto ID number',
        'Transaction ID'
    ];
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * MigratedOrderInfo constructor.
     * @param Template\Context $context
     * @param JsonSerializer $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JsonSerializer $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Migrated order additional information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Migrated order additional information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $order = $this->getParentBlock()->getOrder();
        $result = $this->checkIfOrderMigrated($order->getIncrementId());
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getBillingNotice()
    {
        $order = $this->getParentBlock()->getOrder();
        $result = $order->getKwBillingNotice();

        return $result;
    }

    /**
     * @return mixed
     */
    public function getDeliveryNotice()
    {
        $order = $this->getParentBlock()->getOrder();
        $result = $order->getKwDeliveryNotice();

        return $result;
    }

    /**
     * @return array|bool|float|int|mixed|null|string
     */
    public function getAdditionalInformation()
    {
        $result = [];
        $order = $this->getParentBlock()->getOrder();
        $kwAdditionalData = $order->getKwOrderAdditionalInfo();
        if ($kwAdditionalData && is_string($kwAdditionalData)) {
            $result = $this->jsonSerializer->unserialize($kwAdditionalData);
        } else {
            return $result;
        }
        return is_array($result) ? array_combine(self::ADDITIONAL_DATA_TITLE, $result) : [];
    }

    /**
     * @param string $orderIncrement
     * @return bool
     */
    public function checkIfOrderMigrated($orderIncrement = '')
    {
        return strpos($orderIncrement, 'kw') !== false;
    }
}
