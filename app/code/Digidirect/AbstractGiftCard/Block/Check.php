<?php

namespace Digidirect\AbstractGiftCard\Block;

/**
 * Check result block for a AbstractGiftCard
 */
class Check extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Check constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->_messageManager = $messageManager;
    }

    /**
     * Get current card instance from registry
     *
     * @return \Magento\GiftCardAccount\Model\Giftcardaccount
     */
    public function getCard()
    {
        return $this->_coreRegistry->registry('current_giftcardaccount');
    }

    /**
     * Get formatted expiration date
     *
     * @return string
     */
    public function getExpirationDate()
    {
        $date = new \DateTime(
            $this->getCard()->getDateExpires(),
            new \DateTimeZone($this->_localeDate->getConfigTimezone())
        );
        return parent::formatDate($date);
    }

    /**
     * @return \Magento\Framework\Message\Collection
     */
    public function getMesssages()
    {
        return $this->_messageManager->getMessages(true);
    }
}
