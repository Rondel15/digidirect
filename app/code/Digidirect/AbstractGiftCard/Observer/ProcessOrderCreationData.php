<?php

namespace Digidirect\AbstractGiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProcessOrderCreationData implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     */
    protected $_giftCardAccountFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $abstractGiftCardEntityFactory
     */
    protected $_abstractGiftCardEntityFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $_abstractGiftCardEntityRepository;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * ProcessOrderCreationData constructor.
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $abstractGiftCardEntityFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonDecoder
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntityFactory $abstractGiftCardEntityFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_helper = $helper;
        $this->_giftCardAccountFactory = $giftcardaccountFactory;
        $this->_abstractGiftCardEntityFactory = $abstractGiftCardEntityFactory;
        $this->_abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->_messageManager = $messageManager;
        $this->_jsonDecoder = $jsonDecoder;
    }

    /**
     * Process post data and set usage of GC into order creation model
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $model = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();
        if (isset($request['abstract_gift_card_data'])) {
            $data = $this->_jsonDecoder->decode($request['abstract_gift_card_data']);
            if (isset($data['service_code'])) {
                try {
                    $service = $this->_helper->getServiceInstance($data['service_code']);
                    $service->getAbstractGiftCardEntity()->addData($data);
                    if (!$service->canCheckStatus()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('This service is not available')
                        );
                    }
                    $service->setStore($quote->getStoreId());
                    $service->validate()->checkStatus();
                    $service->getGiftCardAccount()->addToCart(true, $quote);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('We cannot apply this gift card.'));
                }
            }
        }

        return $this;
    }
}
