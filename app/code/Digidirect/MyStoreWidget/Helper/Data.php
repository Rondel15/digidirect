<?php

namespace Digidirect\MyStoreWidget\Helper;

use Digidirect\AbstractEntity\Helper\Url;
use Digidirect\MyStoreWidget\Api\Data\MyStoreInterface;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\MyStoreWidget\Helper\Config as MySotreConfig;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const MYSTORE_COOKIE_NAME = 'mystore_entity_id';
    const MYSTORE_SEARCH_TEXT_COOKIE_NAME = 'mystore_search_text';
    const ABSTRACT_ENTITY_REGISTRY_ID = 'abstract_entity_id';

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieMetadataManager;

    /**
     * @var Config
     */
    protected $myStoreConfig;

    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var AbstractEntityInterface|false
     */
    protected $currentStore = [];

    /**
     * @var array
     */
    protected $currentMyStore = [];

    /**
     * @var Url
     */
    protected $aeHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $phpCookieManager
     * @param Config $myStoreConfig
     * @param Url|null $url
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        MyStoreRepositoryInterface $myStoreRepository,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        CustomerSessionFactory $customerSessionFactory,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $phpCookieManager,
        MySotreConfig $myStoreConfig,
        Url $url = null,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context);
        $this->myStoreRepository = $myStoreRepository;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->customerSession = $customerSessionFactory->create();
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieMetadataManager = $phpCookieManager;
        $this->myStoreConfig = $myStoreConfig;
        $this->aeHelper = $url ?: ObjectManager::getInstance()->get(Url::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @param string $type
     * @return MyStoreInterface|null
     */
    public function getCurrentMyStore($type = MyStoreInterface::DEFAULT_TYPE)
    {
        if (isset($this->currentMyStore[$type])) {
            return $this->currentMyStore[$type];
        }
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return null;
        }
        $this->currentMyStore[$type] = $this->myStoreRepository->getByCustomerId($customerId, $type);

        return $this->currentMyStore[$type];
    }

    /**
     * @param bool $reload
     * @param string $type
     * @return AbstractEntityInterface|false
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCurrentStore($reload = false, $type = MyStoreInterface::DEFAULT_TYPE)
    {
        if (!isset($this->currentStore[$type]) || $reload) {
            $this->currentStore[$type] = false;
            $myStoreId = false;

            if ($this->customerSession->isLoggedIn()) {
                if ($customerId = $this->customerSession->getCustomerId()) {
                    $myStoreId = $this->myStoreRepository
                        ->getByCustomerId($customerId, $type)
                        ->getAbstractEntityId();
                }
            }

            if (!$myStoreId) {
                $myStoreId = $this->getAbstractEntityIdInSession($type);
            }

            if (!$myStoreId) {
                $myStoreId = $this->getMyStoreCookie($type);
            }

            if ($myStoreId) {
                try {
                    $this->currentStore[$type] = false;
                    $store = $this->abstractEntityRepository->getById($myStoreId);
                    if ($store->getId() && $store->getStatus()) {
                        $storeUrl = $this->aeHelper->getAbstractEntityUrl(
                            $store->getId(),
                            $this->storeManager->getStore()->getId()
                        );
                        $store->setUrlKey($storeUrl);
                        $this->currentStore[$type] = $store;
                    }
                } catch (LocalizedException $e) {
                    $this->currentStore[$type] = false;
                }
            }
        }
        return $this->currentStore[$type];
    }

    /**
     * @param int $entityId
     * @param string $type
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setCurrentStore($entityId, $type = MyStoreInterface::DEFAULT_TYPE)
    {
        if ($this->customerSession->isLoggedIn() && $customerId = $this->customerSession->getCustomerId()) {
            $myStore = $this->myStoreRepository->getByCustomerId($customerId, $type);
            $myStore->setAbstractEntityId($entityId);
            $myStore->setType($type);
            $this->myStoreRepository->save($myStore);
        }
        $this->setAbstractEntityIdInSession($entityId, $type);
        $this->setMyStoreCookie($entityId, $type);

        $this->currentStore[$type] = null;
    }

    /**
     * @param int $myStoreId
     * @param string $type
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setMyStoreCookie($myStoreId, $type = MyStoreInterface::DEFAULT_TYPE)
    {
        $metadataMyStore = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($this->myStoreConfig->getCookieLifeTime())
            ->setPath('/')
            ->setHttpOnly(false);

        $postfix = $type ? "_$type" : '';
        $this->cookieMetadataManager->setPublicCookie(
            self::MYSTORE_COOKIE_NAME . $postfix,
            $myStoreId,
            $metadataMyStore
        );
    }

    /**
     * @param string $text
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setSearchTextCookie($text)
    {
        $metadataMyStore = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($this->myStoreConfig->getCookieLifeTime())
            ->setPath('/')
            ->setHttpOnly(false);

        $this->cookieMetadataManager->setPublicCookie(
            static::MYSTORE_SEARCH_TEXT_COOKIE_NAME,
            $text,
            $metadataMyStore
        );
    }

    /**
     * @param string $type
     * @return false|string
     */
    public function getMyStoreCookie($type = MyStoreInterface::DEFAULT_TYPE)
    {
        $postfix = $type ? "_$type" : '';
        return $this->cookieMetadataManager->getCookie(self::MYSTORE_COOKIE_NAME . $postfix, false);
    }

    /**
     * @return string
     */
    public function getSearchTextCookie()
    {
        return $this->cookieMetadataManager->getCookie(static::MYSTORE_SEARCH_TEXT_COOKIE_NAME);
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function snakeCaseToCamel($text)
    {
        return str_replace('_', '', ucwords(strtolower($text), '_'));
    }

    /**
     * @param string $value
     * @param string $key
     * @return void
     */
    public function setAbstractEntityIdInSession($value, $key = '')
    {
        $methodName = 'setAbstractEntityId';
        if ($key) {
            $methodName .= $this->snakeCaseToCamel($key);
        }
        $this->customerSession->{$methodName}($value);
    }

    /**
     * @param string $key
     * @return int
     */
    public function getAbstractEntityIdInSession($key)
    {
        $methodName = 'getAbstractEntityId';
        if ($key) {
            $methodName .= $this->snakeCaseToCamel($key);
        }
        return (int)$this->customerSession->{$methodName}();
    }
}
