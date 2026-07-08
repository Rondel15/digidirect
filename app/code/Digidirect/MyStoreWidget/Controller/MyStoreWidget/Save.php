<?php

namespace Digidirect\MyStoreWidget\Controller\MyStoreWidget;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Helper\Config;
use Digidirect\MyStoreWidget\Model\MyStoreFactory;
use Digidirect\MyStoreWidget\Helper\Data as MyStoreDataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Registry;

/**
 * Class Save
 *
 * @package Digidirect\MyStoreWidget\Controller\MyStoreWidget
 */
class Save extends Index
{
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieMetadataManager;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var MyStoreDataHelper
     */
    protected $myStoreDataHelper;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param MyStoreFactory $myStoreFactory
     * @param Session $customerSession
     * @param PhpCookieManager $phpCookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param UrlHelper $urlHelper
     * @param Registry $registry
     * @param MyStoreDataHelper $myStoreDataHelper
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param Config $configHelper
     */
    public function __construct(
        Context $context,
        MyStoreRepositoryInterface $myStoreRepository,
        MyStoreFactory $myStoreFactory,
        Session $customerSession,
        PhpCookieManager $phpCookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        UrlHelper $urlHelper,
        Registry $registry,
        MyStoreDataHelper $myStoreDataHelper,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        Config $configHelper
    ) {
        parent::__construct($context, $myStoreRepository, $myStoreFactory, $customerSession);
        $this->cookieMetadataManager = $phpCookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->urlHelper = $urlHelper;
        $this->registry = $registry;
        $this->myStoreDataHelper = $myStoreDataHelper;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->configHelper = $configHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $post = $this->getRequest()->isPost();
        if (!$post) {
            return $this->_redirect('*/*/');
        }

        $isAjax = $this->getRequest()->getParam('isAjax', false);
        $responseData = [];

        try {
            $customerId = $this->_getSession()->getCustomerId();
            $myStoreId = (int)$this->getRequest()->getParam('abstract_entity_id');
            $store = new DataObject();
            if ($this->getRequest()->getParam('find_store')) {
                $address = new DataObject($this->getRequest()->getParams());
                if ($store = $this->myStoreRepository->getStoreByAddress($address)) {
                    $myStoreId = $store->getId();
                } else {
                    throw new LocalizedException(__('Sorry, we don\'t have a Store for entered location.'));
                }
            }
            if ($this->configHelper->isSearchTypeTextInput()) {
                $searchText = $this->getRequest()->getParam('store_name');
                $savedStores = $this->myStoreRepository->saveForTypeTextInput($customerId, $searchText);

                $savedTypes = [];
                foreach ($savedStores as $store) {
                    $type = $store['type'];
                    if (in_array($type, $savedTypes)) {
                        continue;
                    }

                    $savedTypes[] = $type;
                    $storeId = $store[AbstractEntityInterface::ENTITY_ID];
                    $this->myStoreDataHelper->setCurrentStore($storeId);
                    $this->myStoreDataHelper->setCurrentStore($storeId, $type);
                }
                $this->myStoreDataHelper->setSearchTextCookie($searchText);
            } else {
                $myStoreId = $this->myStoreRepository->saveForTypeAutocomplete($myStoreId, $customerId);
                if ($myStoreId) {
                    $this->_getSession()->setAbstractEntityId($myStoreId);
                    $this->myStoreDataHelper->setMyStoreCookie($myStoreId);
                }

            }

            $this->_getSession()->regenerateId();
            if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
            }

            $quote = $this->checkoutSession->getQuote();
            if ($quote->getId()) {
                $quote->collectTotals();
                $this->cartRepository->save($quote);
            }

            $message = __('You saved the location.');
            if (!$isAjax) {
                $this->messageManager->addSuccessMessage($message);
            } else {
                if (empty($store->getData()) && $curStore = $this->abstractEntityRepository->getById($myStoreId)) {
                    $store->setData($curStore->getData());
                    $store['url'] = $this->_url->getUrl($store['url_key']);
                }
                $responseData = [
                    'error' => false,
                    'message' => $message,
                    'store' => $store->getData(),
                ];
            }
            $error = false;
        } catch (LocalizedException $e) {
            $message = $e->getMessage();
            $error = true;
        } catch (\Exception $e) {
            $message = __('We can\'t process your request right now.');
            $error = true;
        } finally {
            if (!empty($error)) {
                if (!$isAjax) {
                    $this->messageManager->addErrorMessage($message);
                } else {
                    $responseData = [
                        'error' => true,
                        'message' => $message,
                    ];
                }
            }
        }

        if ($isAjax) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;
        } else {
            $redirectUrl = $this->_redirect->getRedirectUrl();
            $redirectUrl = $this->urlHelper->removeRequestParam($redirectUrl, 'rand');
            $redirectUrl = $this->urlHelper->addRequestParam($redirectUrl, ['rand' => time()]);
            return $this->_redirect($redirectUrl);
        }
    }
}
