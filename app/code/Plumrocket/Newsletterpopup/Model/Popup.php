<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\CollectionFactory;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method as DisplayMethod;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\CombineFactory;
use Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule;
use Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface;
use Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator as ThumbnailGenerator;
use Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator as ThemeThumbnailGenerator;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup as PopupResource;
use Plumrocket\Newsletterpopup\Model\TemplateFactory;
use Plumrocket\Newsletterpopup\Plugin\FilterDirectives;

class Popup extends AbstractModel implements PopupInterface
{
    private $_dataHelper;
    private $_templateFactory;
    private $_combineFactory;
    private $_actionCollectionFactory;
    private $_filterProvider;
    private $_dateTime;

    /**
     * @var SerializerInterface
     */
    private $phpSerializer;

    /**
     * @var ThumbnailGenerator
     */
    private $thumbnailGenerator;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator
     */
    private $themeThumbnailGenerator;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule
     */
    private $getDefaultRule;

    /**
     * @var \Plumrocket\Newsletterpopup\Plugin\FilterDirectives
     */
    private $filterDirectivesPlugin;

    /**
     * @param \Magento\Framework\Model\Context                                  $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Framework\Data\FormFactory                               $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface              $localeDate
     * @param \Plumrocket\Newsletterpopup\Helper\Data                           $dataHelper
     * @param \Plumrocket\Newsletterpopup\Model\TemplateFactory                 $templateFactory
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Condition\CombineFactory  $combineFactory
     * @param \Magento\Rule\Model\Action\CollectionFactory                      $actionCollectionFactory
     * @param \Magento\Cms\Model\Template\FilterProvider                        $filterProvider
     * @param \Magento\Framework\Stdlib\DateTime                                $dateTime
     * @param \Magento\Framework\Serialize\SerializerInterface                  $phpSerializer
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator       $thumbnailGenerator
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator $themeThumbnailGenerator
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule            $getDefaultRule
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null      $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null                $resourceCollection
     * @param array                                                             $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Data $dataHelper,
        TemplateFactory $templateFactory,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        FilterProvider $filterProvider,
        DateTime $dateTime,
        SerializerInterface $phpSerializer,
        ThumbnailGenerator $thumbnailGenerator,
        ThemeThumbnailGenerator $themeThumbnailGenerator,
        GetDefaultRule $getDefaultRule,
        FilterDirectives $filterDirectivesPlugin,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_templateFactory = $templateFactory;
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_filterProvider = $filterProvider;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->phpSerializer = $phpSerializer;
        $this->thumbnailGenerator = $thumbnailGenerator;
        $this->themeThumbnailGenerator = $themeThumbnailGenerator;
        $this->getDefaultRule = $getDefaultRule;
        $this->filterDirectivesPlugin = $filterDirectivesPlugin;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Init default values.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(PopupResource::class);

        /** @var \Plumrocket\Newsletterpopup\Model\Popup\Theme $defaultTemplate */
        $defaultTemplate = $this->_templateFactory->create()->load(20);

        $defaults = [
            'type'                    => Type::MODAL,
            'success_page'            => '__stay__',
            'display_popup'           => DisplayMethod::PAGE_SCROLL,
            'template_id'             => $defaultTemplate->getId(),
            'code'                    => $defaultTemplate->getHtml(),
            'style'                   => $defaultTemplate->getCss(),
            'send_email'              => 1,
            'delay_time'              => '5',
            'mobile_leave_delay_time' => '5',
            'page_scroll'             => '50',
            'css_selector'            => '',
            'cookie_time_frame'       => '30',
            'store_id'                => '0',
            'text_title'              => __('Join our email list and SAVE'),
            'text_submit'             => __('Submit'),
            'text_cancel'             => __('No Thanks'),
            'code_length'             => 12,
            'code_format'             => 'alphanum',
            'code_prefix'             => '',
            'code_suffix'             => '',
            'code_dash'               => 0,
            'email_template'          => 'prnewsletterpopup_general_email_template',
            'animation'               => 'fadeInDownBig',
            'signup_method'           => 'signup_only',
            'subscription_mode'       => 'all_selected',
            'text_description'        => '<p>Join Magento Store List and Save!<br>' .
                'Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>',
            'text_success'            => '<div class="message-title"><h2>ENJOY $10 OFF</h2>' .
                '<p>entire purchase</p></div><div class="coupon_wrp">' .
                '<div class="coupon-message">Enter Coupon Code At Checkout:</div>' .
                '<div class="coupon-use">{{coupon_code}}</div></div><div class="coupon-expiration">' .
                '<span>Hurry! This Offer Ends in 2 HOURS!</span></div>',
            'conditions'              => $this->getDefaultRule->execute(),
        ];
        $data = $this->getData();

        foreach ($defaults as $key => $val) {
            if (!array_key_exists($key, $data)) {
                $this->setData($key, $val);
            }
        }
        $this->initConditionsSerialized();
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return (bool) $this->_getData('status');
    }

    /**
     * @inheritDoc
     */
    public function isModal(): bool
    {
        return $this->getType() === Type::MODAL;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->_getData(self::TYPE) ?: Type::MODAL;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): PopupInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->_getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): PopupInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get default product SKU.
     *
     * @return string
     */
    public function getDefaultProduct(): string
    {
        return (string) $this->_getData(self::DEFAULT_PRODUCT);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultProduct(string $sku): PopupInterface
    {
        return $this->setData(self::DEFAULT_PRODUCT, $sku);
    }

    /**
     * @inheritDoc
     */
    public function useCurrentProduct(): bool
    {
        return (bool) $this->_getData(self::USE_CURRENT_PRODUCT);
    }

    /**
     * @inheritDoc
     */
    public function setUseCurrentProduct(bool $flag): PopupInterface
    {
        return $this->setData(self::USE_CURRENT_PRODUCT, $flag);
    }

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return (string) $this->_getData(self::HTML);
    }

    /**
     * @inheritDoc
     */
    public function setHtml(string $html): TemplateInterface
    {
        return $this->setData(self::HTML, $html);
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return (string) $this->_getData(self::CSS);
    }

    /**
     * @inheritDoc
     */
    public function setCss(string $css): TemplateInterface
    {
        return $this->setData(self::CSS, $css);
    }

    /**
     * @return string
     */
    public function getSuccessPage(): string
    {
        return (string) $this->_getData(self::SUCCESS_PAGE);
    }

    /**
     * @return string
     */
    public function getCustomSuccessPage(): string
    {
        return (string) $this->_getData(self::CUSTOM_SUCCESS_PAGE);
    }

    /**
     * Retrieve Serialized Conditions
     * Deprecated and Need to be removed in future releases
     * added for compatibility with 2.1.x and 2.2.x
     * @return string
     */
    public function getConditionsSerialized()
    {
        $value = $this->getData('conditions_serialized');
        if (!$value && $this->getData('conditions')) {
            $value = $this->phpSerializer->serialize($this->getData('conditions'));
        }

        if (isset($this->serializer)) {
            try {
                $uv = $this->phpSerializer->unserialize($value);
                $value = $this->serializer->serialize($uv);
            } catch (\Exception $e) {
            }
        }

        return $value;
    }

    public function initConditionsSerialized()
    {
        $this->setConditionsSerialized($this->getConditionsSerialized());
        return $this;
    }

    /**
     * Retrieve Serialized Actions
     * Deprecated and Need to be removed in future releases
     * added for compatibility with 2.1.x and 2.2.x
     * @return string
     */
    public function getActionsSerialized()
    {
        $value = $this->getData('actions_serialized');

        if (isset($this->serializer)) {
            try {
                $uv = $this->phpSerializer->unserialize($value);
                $value = $this->serializer->serialize($uv);
            } catch (\Exception $e) {
            }
        }

        return $value;
    }

    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function getData($key = '', $index = null)
    {
        if (!$this->_dataHelper->isAdmin()) {
            if (in_array($key, ['text_description', 'text_success'])) {
                if (null !== parent::getData($key)) {
                    $this->filterDirectivesPlugin->enable();
                    $process = $this->_filterProvider->getPageFilter();
                    $result = $process->filter(parent::getData($key));
                    $this->filterDirectivesPlugin->disable();
                    return $result;
                }
                return '';
            }
        }

        return parent::getData($key, $index);
    }

    public function getOffsetForCookieTimeFrame()
    {
        return $this->_dataHelper->getOffsetFromExtendedTime(
            $this->getCookieTimeFrame(),
            'cookie_time_frame'
        );
    }

    public function cleanCache()
    {
        $this->_cacheManager->clean('prnewsletterpopup_' . $this->getId());
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator::generate
     *
     * @return bool
     */
    public function generateThumbnail()
    {
        return $this->thumbnailGenerator->generate((int) $this->getId());
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator::getImagePath
     *
     * @param false $forWeb
     * @return string
     */
    public function getThumbnailFilePath($forWeb = false)
    {
        return $this->thumbnailGenerator->getImagePath((int) $this->getId(), $forWeb);
    }

    /**
     * @param false $forWeb
     * @return string
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Generator::getImageCachePath
     */
    public function getThumbnailCacheFilePath($forWeb = false)
    {
        return $this->thumbnailGenerator->getImageCachePath((int) $this->getId(), $forWeb);
    }

    public function beforeSave()
    {
        if ($this->getTemplateId()) {
            /** @var \Plumrocket\Newsletterpopup\Model\Popup\Theme $template */
            if ($template = $this->_templateFactory->create()->load($this->getTemplateId())) {
                $templateCode = $this->_dataHelper->getNString($template->getHtml());
                $popupCode = $this->_dataHelper->getNString($this->getHtml());
                $templateStyle = $this->_dataHelper->getNString($template->getCss());
                $popupStyle = $this->_dataHelper->getNString($this->getCss());

                if (strnatcmp($templateCode, $popupCode) || strnatcmp($templateStyle, $popupStyle)) {
                    $countPopups = $this
                        ->getCollection()
                        ->addFieldToFilter('template_id', $template->getId())
                        ->getSize();

                    if ($template->getBaseTemplateId() == -1
                        || ($this->isObjectNew() && $countPopups >= 1)
                        || (!$this->isObjectNew() && $countPopups > 1)
                    ) {
                        // Create new.
                        $template->setBaseTemplateId($template->getId());
                        $template->setId(null);
                        $template->setName($template->getName() . ' - '. $this->getName());
                    }

                    $template->addData([
                        'code'  => $popupCode,
                        'style' => $popupStyle,
                    ]);

                    if ($customThemeId = $template->save()->getId()) {
                        $this->themeThumbnailGenerator->generate((int) $customThemeId);
                        $this->setTemplateId($customThemeId);
                    }
                }
            }
        }

        $this->unsetData('template_name');
        $this->unsetData('code');
        $this->unsetData('style');

        return parent::beforeSave();
    }

    protected function _afterLoad()
    {
        if ($this->getTemplateId()) {
            if ($template = $this->_templateFactory->create()->load($this->getTemplateId())) {
                $this->addData([
                    'template_name' => $template->getName(),
                    'code'    => $template->getCode(),
                    'style'    => $template->getStyle(),
                ]);
            }
        }
        return parent::_afterLoad();
    }

    public function textHasPlaceholder($text, $placeholder)
    {
        return false !== stripos($text, $placeholder);
    }

    /**
     * Retrive bool true if coupon success text has placeholder {{coupon_code}}
     *
     * @return boolean
     */
    public function hasSuccessTextPlaceholders()
    {
        $successText = $this->getTextSuccess();
        $placeholders = $this->_dataHelper->getSuccessTextPlaceholders();

        foreach ($placeholders as $placeholder) {
            if ($this->textHasPlaceholder($successText, $placeholder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve success message with replaced placeholders
     *
     * @param void
     * @return string
     */
    public function getPreparedTextSuccess()
    {
        $result = $this->getTextSuccess();
        $placeholders = $this->_dataHelper->getSuccessTextPlaceholders();

        foreach ($placeholders as $placeholder) {
            if ($this->textHasPlaceholder($result, $placeholder)) {
                $propertyName = trim($placeholder, '{}');
                $value = (string) $this->getData('rule_' . $propertyName);

                $result = str_ireplace($placeholder, $value, $result);
            }
        }

        return $result;
    }

    /**
     * Retrieve coupon expiration date by user timezone
     *
     * @param mixed $expirationDate
     * @param bool  $formatted
     * @return string formatted Date and Time string
     */
    public function getCouponExpLocaleDateTime($expirationDate = null, $formatted = false)
    {
        if (! $expirationDate) {
            $expirationDate = $this->getCouponExpDateTime();
        }

        if (false === $expirationDate) {
            return false;
        }

        $expirationDate = strtotime($expirationDate);
        $localeDate = $this->_localeDate->date($expirationDate);

        return !$formatted
            ? $this->_localeDate->date($expirationDate)->format(DateTime::DATETIME_PHP_FORMAT)
            : $this->_localeDate->formatDate($localeDate, \IntlDateFormatter::MEDIUM, true);
    }

    /**
     * Retrieve coupon expiration date by GMT timezone
     *
     * @return false|string formatted Date and Time string
     */
    public function getCouponExpDateTime()
    {
        $expirationDate = $this->getData('coupon_expiration_time');

        $offset = $expirationDate
            ? $this->_dataHelper->getOffsetFromExtendedTime(
                $expirationDate,
                'coupon_expiration_time'
            ) : false;

        return ($offset <= 0)
            ? false
            : $this->_dateTime->formatDate(
                $this->_dateTime->strToTime('now') + $offset
            );
    }

    /**
     * @param $key
     * @param null $integrationId
     * @return array|string|null
     */
    public function getPreparedJsonValue($key, $integrationId = null)
    {
        $configValue = json_decode((string) $this->getData($key), true);

        if (! is_array($configValue)) {
            $configValue = [];
        }

        if (null === $integrationId) {
            return $configValue;
        }

        $integrationId = (string)$integrationId;

        return isset($configValue[$integrationId]) ? (string)$configValue[$integrationId] : null;
    }

    /**
     * @param null $integrationId
     * @return array|null|string
     */
    public function getPreparedIntegrationMode($integrationId = null)
    {
        return $this->getPreparedJsonValue('integration_mode', $integrationId);
    }

    /**
     * Check if integration is enabled.
     *
     * @param null $integrationId
     * @return array|null|string
     */
    public function getPreparedIntegrationEnable($integrationId = null)
    {
        return $this->getPreparedJsonValue('integration_enable', $integrationId);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayPopup(): string
    {
        return (string) $this->_getData('display_popup');
    }

    /**
     * @inheritdoc
     */
    public function getMobileLeaveDelayTime(): int
    {
        return (int) $this->_getData('mobile_leave_delay_time');
    }

    /**
     * @inheritdoc
     */
    public function getDelayTime(): int
    {
        return (int) $this->_getData('delay_time');
    }
}
