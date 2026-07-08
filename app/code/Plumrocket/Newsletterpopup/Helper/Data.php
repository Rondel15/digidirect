<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Config\Model\ConfigFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Base\Model\ConfigUtils;
use Plumrocket\Newsletterpopup\Helper\Config as ConfigHelper;
use Plumrocket\Newsletterpopup\Model\PopupFactory;

class Data extends AbstractHelper
{
    const VISITOR_ID_PARAM_NAME = 'nsp_v';
    const SECTION_ID = 'prnewsletterpopup';

    const PLACEHOLDER_COUPON_CODE = '{{coupon_code}}';
    const PLACEHOLDER_COUPON_EXPIRATION_TIME = '{{coupon_expiration_date}}';

    const DEFAULT_GENERAL_LIST_NAME = 'np_general_list';

    const FORMAT_DAY = 'day';
    const FORMAT_HOUR = 'hour';
    const FORMAT_MIN = 'min';
    const FORMAT_SEC = 'sec';

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    protected $_allowedExtTimeFields = [
        self::FORMAT_DAY,
        self::FORMAT_HOUR,
        self::FORMAT_MIN,
        self::FORMAT_SEC,
    ];

    /**
     * @var array
     */
    protected $_defaultValues = [
        'status' => 1,
        'display_popup' => 'after_time_delay',
        'delay_time' => 0,
        'text_title' => 'GET $10 OFF YOUR FIRST ORDER',
        'text_description' => '<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>',
        'text_success' => '<div class=\"message-title\"><h2>ENJOY $10 OFF</h2><p>entire purchase</p></div><div class=\"coupon_wrp\"><div class=\"coupon-message\">Enter Coupon Code At Checkout:</div><div class=\"coupon-use\">{{coupon_code}}</div></div><div class=\"coupon-expiration\"><span>Hurry! This Offer Ends in 2 HOURS!</span></div>',
        'text_submit' => 'Sign Up Now',
        'text_cancel' => 'Hide',
        'animation' => 'fadeInDownBig',
    ];

    /**
     * @var array
     */
    protected $_successTextPlaceholders = [
        self::PLACEHOLDER_COUPON_CODE,
        self::PLACEHOLDER_COUPON_EXPIRATION_TIME,
    ];

    /**
     * @var string
     */
    protected $_configSectionId = 'prnewsletterpopup';

    /**
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var Rule
     */
    protected $_salesRule;

    /**
     * @var Coupon
     */
    protected $_coupon;

    /**
     * @var Encryptor
     */
    protected $_encryptor;

    /**
     * @var PhpCookieManager
     */
    protected $_phpCookieManager;

    /**
     * @var PublicCookieMetadataFactory
     */
    protected $_publicCookieMetadataFactory;

    /**
     * @var DataEncodedFactory
     */
    protected $_dataEncodedHelperFactory;

    /**
     * @var PopupFactory
     */
    protected $_popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Plumrocket\Base\Model\ConfigUtils
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param \Magento\Config\Model\ConfigFactory                          $configFactory
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Store\Model\Store                                   $store
     * @param \Magento\SalesRule\Model\Rule                                $salesRule
     * @param \Magento\SalesRule\Model\Coupon                              $coupon
     * @param \Magento\Framework\Encryption\Encryptor                      $encryptor
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager            $phpCookieManager
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $publicCookieMetadataFactory
     * @param \Plumrocket\Newsletterpopup\Helper\DataEncodedFactory        $dataEncodedHelperFactory
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory               $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\TemplateFactory            $templateFactory
     * @param \Magento\Framework\App\ResourceConnection                    $resourceConnection
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $configHelper
     * @param \Magento\Framework\Serialize\SerializerInterface             $serializer
     * @param \Magento\Framework\Serialize\SerializerInterface             $jsonSerializer
     * @param \Magento\Framework\App\State                                 $state
     * @param \Plumrocket\Base\Model\ConfigUtils                           $configUtils
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        ConfigFactory $configFactory,
        StoreManagerInterface $storeManager,
        Store $store,
        Rule $salesRule,
        Coupon $coupon,
        Encryptor $encryptor,
        PhpCookieManager $phpCookieManager,
        PublicCookieMetadataFactory $publicCookieMetadataFactory,
        DataEncodedFactory $dataEncodedHelperFactory,
        PopupFactory $popupFactory,
        \Plumrocket\Newsletterpopup\Model\TemplateFactory $templateFactory,
        ResourceConnection $resourceConnection,
        ConfigHelper $configHelper,
        SerializerInterface $serializer,
        SerializerInterface $jsonSerializer,
        State $state,
        ConfigUtils $configUtils
    ) {
        $this->_configFactory = $configFactory;
        $this->_storeManager = $storeManager;
        $this->_store = $store;
        $this->_salesRule = $salesRule;
        $this->_coupon = $coupon;
        $this->_encryptor = $encryptor;
        $this->_phpCookieManager = $phpCookieManager;
        $this->_publicCookieMetadataFactory = $publicCookieMetadataFactory;
        $this->_dataEncodedHelperFactory = $dataEncodedHelperFactory;
        $this->_popupFactory = $popupFactory;
        $this->_templateFactory = $templateFactory;
        $this->resourceConnection = $resourceConnection;
        $this->configHelper = $configHelper;
        $this->serializer = $serializer;
        $this->state = $state;
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
        $this->objectManager = $objectManager;
        $this->configUtils = $configUtils;
    }

    /**
     * Check if module is enabled.
     *
     * This method was added to safely replace moduleEnabled method in the Subscriber model.
     *
     * @sinse 4.5.5
     * @return bool
     * @deprecated since 4.6.0
     * @see \Plumrocket\Newsletterpopup\Helper\Config::isModuleEnabled
     */
    public function isModuleEnabled(): bool
    {
        return $this->configHelper->isModuleEnabled();
    }

    /**
     * Receive magento config value
     *
     * @param string      $path
     * @param string|int  $store
     * @param string|null $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        return $this->configUtils->getConfig($path, $scope, $store);
    }

    /**
     * Get current popup
     *
     * @return mixed
     */
    public function getCurrentPopup()
    {
        if ($this->configHelper->isModuleEnabled() && ! $this->isAdmin()) {
            /* @var \Plumrocket\Newsletterpopup\Helper\DataEncoded $encodedHelper */
            $encodedHelper = $this->_dataEncodedHelperFactory->create();
            return $encodedHelper->getCurrentPopup();
        }
        return $this->_popupFactory->create();
    }

    public function getLockedPopupIds()
    {
        return $this->configHelper->isModuleEnabled() && ! $this->isAdmin()
            ? $this->_dataEncodedHelperFactory->create()->getLockedPopupIds()
            : [];
    }

    public function isAdmin()
    {
        return $this->state->getAreaCode() === Area::AREA_ADMINHTML;
    }

    public function validateUrl($url)
    {
        // !! I think need to use storeManager here.
        if (!$this->_store->isCurrentlySecure()) {
            $url = str_replace('https://', 'http://', $url);
        } else {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    public function getPopupMailchimpList($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'MailchimpList');
    }

    public function getPopupMailchimpListKeys($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'MailchimpList', true);
    }

    /**
     * @param $popupId
     * @param $justActive
     * @return array
     * @deprecated since 4.6.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\GetFields::execute
     */
    public function getPopupFormFields($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'FormField');
    }

    public function getPopupFormFieldsKeys($popupId, $justActive)
    {
        return $this->_getCollectionData($popupId, $justActive, 'FormField', true);
    }

    private function _getCollectionData($popupId, $justActive, $model, $justKeys = false)
    {
        $collection = $this->objectManager->get('Plumrocket\Newsletterpopup\Model\\' . $model)
            ->getCollection()
            ->addFieldToFilter('popup_id', $popupId);

        if ('MailchimpList' == $model) {
            $collection->addFieldToFilter('integration_id', 'mailchimp');
        }

        if ($justActive) {
            $collection = $collection->addFieldToFilter('enable', 1);
        }

        $collection->getSelect()->order(['sort_order', 'label']);

        $result = [];

        foreach ($collection as $item) {
            if ($justKeys) {
                $result[] = $item->getName();
            } else {
                $result[$item->getName()] = $item;
            }
        }

        return $result;
    }

    /**
     * Load popup by id.
     *
     * @param int|string $id
     * @return \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function getPopupById($id)
    {
        $popup = $this->_popupFactory->create()->load($id);
        // load coupon code
        return $this->assignCoupon($popup);
    }

    /**
     * Add coupon to popup.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface $item
     * @return \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function assignCoupon($item)
    {
        $rule = $this->_salesRule->load((int)$item->getCouponCode());
        if (!$rule->getUseAutoGeneration()) {
            $rule->setCoupon(
                $this->_coupon->loadPrimaryByRule($rule)
            );
        }
        return $item->setCoupon($rule);
    }

    /**
     * Loads popup template and do something weird.
     *
     * @param int|string $id
     * @return \Plumrocket\Newsletterpopup\Model\Template
     *
     * @deprecated - create and use repository instead
     * because here we set data to request and no one know the purpose of it.
     * Maybe it needed for preview.
     */
    public function getPopupTemplateById($id)
    {
        /* @var \Plumrocket\Newsletterpopup\Model\Template $item */
        if ($item = $this->_templateFactory->create()->load($id)) {
            $defaultValues = $item->getDefaultConfiguration();
            if ($defaultValues) {
                if (0 === strpos($defaultValues, 'a:')) {
                    $defaultValues = $this->serializer->unserialize($defaultValues);
                } else {
                    $defaultValues = $this->jsonSerializer->unserialize($defaultValues);
                }
            } else {
                $defaultValues = [];
            }
            $item->addData(array_merge($this->_defaultValues, $defaultValues));
            $this->_getRequest()->setParams($defaultValues);
        }

        return $item;
    }

    public function getSuccessTextPlaceholders()
    {
        return $this->_successTextPlaceholders;
    }

    public function getNString($str)
    {
        return str_replace("\r\n", "\n", $str);
    }

    public function visitorId($id = null)
    {
        // !! Check, where is set cookie and not casheable it
        if ($prevId = $this->_phpCookieManager->getCookie(self::VISITOR_ID_PARAM_NAME)) {
            $prevId = (int)$this->_encryptor->decrypt($prevId);
        }

        if ($id) {
            $this->_phpCookieManager->setPublicCookie(
                self::VISITOR_ID_PARAM_NAME,
                $this->_encryptor->encrypt($id),
                $this->_publicCookieMetadataFactory->create()->setDurationOneYear()
            );
        }

        return $prevId;
    }

    /**
     * Retrieve offset of seconds for specific extended_time
     *
     * @param string|array $extendedTimeData
     * @return int
     */
    public function getOffsetFromExtendedTime($extendedTimeData, $fieldName = null)
    {
        if (! is_array($extendedTimeData)) {
            $extendedTimeData = $this->extendedTimeToArray(
                $extendedTimeData,
                $fieldName
            );
        }

        $offset = 0;

        foreach ($extendedTimeData as $key => $value) {
            switch ($key) {
                case self::FORMAT_DAY:
                    $offset += (int) $value * 24 * 60 * 60;
                    break;
                case self::FORMAT_HOUR:
                    $offset += (int) $value * 60 * 60;
                    break;
                case self::FORMAT_MIN:
                    $offset += (int) $value * 60;
                    break;
                case self::FORMAT_SEC:
                    $offset += (int) $value;
                    break;
            }
        }

        return $offset;
    }

    /**
     * Convert string|null $value to array
     *
     * @param  string|null $value
     * @return array
     */
    public function extendedTimeToArray($value = null, $fieldName = null)
    {
        $result = $this->getDefaultExtTime();
        $formats = $this->getExtTimeFormats($fieldName);

        $value = explode(',', (string)$value);

        foreach($formats as $format) {
            if (is_string($format)) {
                $format = explode(',', $format);
            }

            if (count($format) === count($value)) {
                $result = array_merge(
                    $result,
                    array_combine($format, $value)
                );
            }
        }

        return $result;
    }

    /**
     * Retrieve array of fields that can be using for extended time format
     *
     * @param void
     * @return array
     */
    public function getAllowedExtTimeFields()
    {
        return $this->_allowedExtTimeFields;
    }

    /**
     * Retrieve array where  each item equal 0
     *
     * @param void
     * @return array
     */
    public function getDefaultExtTime()
    {
        return [
            self::FORMAT_DAY => 0,
            self::FORMAT_HOUR => 0,
            self::FORMAT_MIN => 0,
            self::FORMAT_SEC => 0,
        ];
    }

    /**
     * Retrieve array of formats for specific field
     * If doesn't defined field then default format will be using
     *
     * @param void
     * @return array
     */
    public function getExtTimeFormats($fieldName = 'default')
    {
        $result = [
            [
                self::FORMAT_DAY,
                self::FORMAT_HOUR,
                self::FORMAT_MIN,
                self::FORMAT_SEC,
            ],
        ];

        switch ($fieldName) {
            case 'cookie_time_frame':
                $result[] = [self::FORMAT_DAY];
                break;

            case 'coupon_expiration_time':
                $result = [
                    [
                        self::FORMAT_DAY,
                        self::FORMAT_HOUR,
                        self::FORMAT_MIN,
                    ],
                ];
                break;
        }

        return $result;
    }
}
