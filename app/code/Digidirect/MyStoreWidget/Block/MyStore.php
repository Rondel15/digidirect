<?php
namespace Digidirect\MyStoreWidget\Block;

use Digidirect\MyStoreWidget\Helper\Data as Helper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;

/**
 * Class MyStore
 * @package Digidirect\MyStoreWidget\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MyStore extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    const SAVE_URL_PATH = 'digidirect_mystorewidget/mystorewidget/save';
    const REMOVE_URL_PATH = 'digidirect_mystorewidget/mystorewidget/delete';
    const SEARCH_URL_PATH = 'digidirect_mystorewidget/mystorewidget/search';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Digidirect\MyStoreWidget\Helper\Config
     */
    protected $helperConfig;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Digidirect\MyStoreWidget\Model\Config\Source\Stores
     */
    protected $storesSourceModel;

    /**
     * @var \Digidirect\MyStoreWidget\Model\MyStoreRepository
     */
    protected $myStoreRepository;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * MyStore constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\MyStoreWidget\Model\Config\Source\Stores $storesSourceModel
     * @param \Digidirect\MyStoreWidget\Model\MyStoreRepository $myStoreRepository
     * @param CustomerSessionFactory $customerSessionFactory
     * @param Helper $helper
     * @param \Digidirect\MyStoreWidget\Helper\Config $helperConfig
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\MyStoreWidget\Model\Config\Source\Stores $storesSourceModel,
        \Digidirect\MyStoreWidget\Model\MyStoreRepository $myStoreRepository,
        CustomerSessionFactory $customerSessionFactory,
        Helper $helper,
        \Digidirect\MyStoreWidget\Helper\Config $helperConfig,
        JsonHelper $jsonHelper,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSessionFactory->create();
        $this->jsonHelper = $jsonHelper;
        $this->helperConfig = $helperConfig;
        $this->configCacheType = $configCacheType;
        $this->storesSourceModel = $storesSourceModel;
        $this->myStoreRepository = $myStoreRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperConfig->isEnable()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Returns the Magento Customer Model
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return bool
     */
    public function isStoreSelected()
    {
        if ($this->getStore()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        $name = '';
        if ($this->helperConfig->isSearchTypeTextInput()) {
            $name = $this->helper->getSearchTextCookie();
        } else {
            if ($myStore = $this->getStore()) {
                $name = $this->formatStoreName($myStore);
            }
        }
        return $name;
    }

    /**
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface|DataObject
     */
    public function getStore()
    {
        return $this->helper->getCurrentStore();
    }

    /**
     * @return array
     */
    public function getEntityOptions()
    {
        return $this->storesSourceModel->toOptionArray();
    }

    /**
     * @param string $name
     * @param string $id
     * @param null|string $title
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreHtmlSelect($name = 'abstract_entity_id', $id = 'mystorewidget', $title = null)
    {
        $cacheKey = 'DIGIDIRECT_SELECT_MYSTORE_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->configCacheType->load($cacheKey);
        $defaultValue = $this->getStore() ? $this->getStore()->getId() : '';
        if ($cache) {
            $options = unserialize($cache);
        } else {
            $options = $this->getEntityOptions();
            $this->configCacheType->save(serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            $name
        )->setId(
            $id
        )->setTitle(
            $title === null ? __('My Store') : $title
        )->setValue(
            $defaultValue
        )->setOptions(
            $options
        )->setExtraParams(
            'data-validate="{\'validate-select\':true}"'
        )->getHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(self::SAVE_URL_PATH);
    }

    /**
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->getUrl(self::REMOVE_URL_PATH, ['customer_id' => $this->getCustomerSession()->getId()]);
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl(self::SEARCH_URL_PATH);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        if ($this->isStoreSelected()) {
            return [static::class . '_' . $this->getStore()->getId()];
        }
        return [];
    }

    /**
     * @param DataObject $store
     * @return Phrase
     */
    public function formatStoreName(DataObject $store)
    {
        if (empty($this->_data['storeNameFormat'])) {
            $this->_data['storeNameFormat'] = '%name';
        }
        return new Phrase($this->_data['storeNameFormat'], $store->getData());
    }

    /**
     * Get list stores in json format
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStores()
    {
        return $this->jsonHelper->serialize($this->myStoreRepository->getStoresCollection());
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();

        $cacheKeyInfo[] = $this->getStore()->getId();

        return $cacheKeyInfo;
    }

    /**
     * @return string
     */
    public function getMyStoreInputId()
    {
        return $this->helperConfig->isSearchTypeTextInput() ? 'mystore-input-type-text' : 'mystore-input';
    }

    /**
     * @return boolean
     */
    public function isSearchTypeTextInput()
    {
        return $this->helperConfig->isSearchTypeTextInput();
    }
}
