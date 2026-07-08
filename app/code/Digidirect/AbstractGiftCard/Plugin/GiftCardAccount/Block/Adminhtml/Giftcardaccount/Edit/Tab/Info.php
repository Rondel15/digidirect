<?php

namespace Digidirect\AbstractGiftCard\Plugin\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab;

use Magento\GiftCardAccount\Block\Adminhtml\Giftcardaccount\Edit\Tab\Info as InfoTab;
use Magento\Framework\Exception\NoSuchEntityException;

class Info
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
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * Info constructor.
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository,
        \Magento\Framework\Registry $registry,
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        $this->_giftCardEntityRepository = $repository;
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
    }

    /**
     * Init form fields
     *
     * @param InfoTab $subject
     * @param InfoTab $result
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterInitForm(InfoTab $subject, $result)
    {
        try {
            $giftCardAccount = $this->_coreRegistry->registry('current_giftcardaccount');
            $entity = $this->_giftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
            $fieldset = $subject->getForm()->getElements()->offsetGet(self::BASE_FIELDSET_OFFSET);
            foreach ($fieldset->getChildren() as $element) {
                if ($element->getId() != 'status') {
                    $element->setData('disabled', true);
                }
            }
            $serviceInstance = $this->_helper->getServiceInstance($entity->getServiceCode());
            $fieldset->addField(
                'service-field',
                'label',
                [
                    'name'  => 'service-field',
                    'label' => __('Service Type'),
                    'title' => __('Service Type'),
                    'value' => $serviceInstance->getTitle()
                ],
                'code'
            );
            // @codingStandardsIgnoreStart
        } catch (NoSuchEntityException $e) {
            //skip
        }
        // @codingStandardsIgnoreEnd
        return $result;
    }
}
