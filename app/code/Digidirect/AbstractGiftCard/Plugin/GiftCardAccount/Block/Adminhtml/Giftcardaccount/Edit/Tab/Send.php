<?php

namespace Digidirect\AbstractGiftCard\Plugin\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab;

use Magento\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab\Send as SendTab;
use Magento\Framework\Exception\NoSuchEntityException;

class Send
{
    const BASE_FIELDSET_OFFSET = 0;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $_giftCardEntityRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Send constructor.
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository,
        \Magento\Framework\Registry $registry
    ) {
        $this->_giftCardEntityRepository = $repository;
        $this->_coreRegistry = $registry;
    }

    /**
     * Init form fields
     *
     * @param SendTab $subject
     * @param SendTab $result
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterInitForm(SendTab $subject, $result)
    {
        try {
            $giftCardAccount = $this->_coreRegistry->registry('current_giftcardaccount');
            $this->_giftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
            $fieldset = $subject->getForm()->getElements()->offsetGet(self::BASE_FIELDSET_OFFSET);
            foreach ($fieldset->getChildren() as $element) {
                $element->setData('disabled', true);
            }
            // @codingStandardsIgnoreStart
        } catch (NoSuchEntityException $e) {
            //skip
        }
        // @codingStandardsIgnoreEnd
        return $result;
    }
}
