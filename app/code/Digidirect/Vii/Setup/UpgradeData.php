<?php

namespace Digidirect\Vii\Setup;

use Digidirect\Vii\Api\Data\OrderInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order\StatusFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    private $statusFactory;

    /**
     * UpgradeData constructor.
     *
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        StatusFactory $statusFactory
    ) {
        $this->statusFactory = $statusFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->addNewCustomGiftCardReversedStatus();
        }

        $setup->endSetup();
    }

    /**
     * Add new custom gift card reversed status
     * @return void
     * @throws \Exception
     */
    protected function addNewCustomGiftCardReversedStatus()
    {
        /** @var \Magento\Sales\Model\Order\Status $status */
        $status = $this->statusFactory->create();
        $status->setData('status', OrderInterface::GIFT_CARD_REVERSED_ORDER_STATUS)
            ->setData('label', __('Gift Card Reversed'))
            ->save();
        $status->assignState(\Magento\Sales\Model\Order::STATE_PROCESSING, false, true);
    }
}
