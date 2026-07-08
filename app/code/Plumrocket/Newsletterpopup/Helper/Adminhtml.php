<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Base\Model\ConfigUtils;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Model\Mcapi;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\Collection as TemplateCollection;

class Adminhtml extends AbstractHelper
{
    const THUMBNAIL_WIDTH = 512;

    private $_mailchimp = false;
    private $_checkIfHtmlToImageInstalledResult = null;

    private $_encryptor;
    private $_cache;
    private $_messageManager;
    private $_storeManager;
    private $_viewRepository;
    private $_templateCollection;
    private $_imageHelper;
    private $backendHelper;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    private $shell;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Base\Model\ConfigUtils
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\App\Helper\Context                                  $context
     * @param \Magento\Framework\Encryption\Encryptor                                $encryptor
     * @param \Magento\Framework\App\Cache                                           $cache
     * @param \Magento\Framework\Message\ManagerInterface                            $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface                             $storeManager
     * @param \Magento\Framework\View\Asset\Repository                               $viewRepository
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\Collection $templateCollection
     * @param \Plumrocket\Newsletterpopup\Helper\Image                               $imageHelper
     * @param \Magento\Backend\Helper\Data                                           $backendHelper
     * @param \Magento\Framework\ShellInterface                                      $shell
     * @param \Plumrocket\Newsletterpopup\Helper\Config                              $config
     * @param \Plumrocket\Base\Model\ConfigUtils                                     $configUtils
     */
    public function __construct(
        Context $context,
        Encryptor $encryptor,
        Cache $cache,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Repository $viewRepository,
        TemplateCollection $templateCollection,
        Image $imageHelper,
        BackendHelper $backendHelper,
        ShellInterface $shell,
        Config $config,
        ConfigUtils $configUtils
    ) {
        $this->_encryptor = $encryptor;
        $this->_cache = $cache;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_viewRepository = $viewRepository;
        $this->_templateCollection = $templateCollection;
        $this->_imageHelper = $imageHelper;
        $this->backendHelper = $backendHelper;
        parent::__construct($context);
        $this->shell = $shell;
        $this->config = $config;
        $this->configUtils = $configUtils;
    }

    public function getMcapi()
    {
        if (!$this->_mailchimp) {
            if ($this->config->isMaichimpEnabled()) {
                $this->_mailchimp = new Mcapi(
                    trim($this->_encryptor->decrypt(
                        $this->configUtils->getStoreConfig('prnewsletterpopup/integration/mailchimp/key')
                    )),
                    true
                );
            }
        }
        return $this->_mailchimp;
    }

    public function getTemplates()
    {
        $collection = $this->_templateCollection
            ->addExpressionFieldToSelect('template_type', 'IF(main_table.base_template_id >= 0, 1, -1)', [])
            ->addExpressionFieldToSelect('is_template', new \Zend_Db_Expr(1), []);

        $collection->getSelect()
            ->joinLeft(
                ['t' => $this->_templateCollection->getResource()->getMainTable()],
                't.entity_id = main_table.base_template_id',
                ['base_template_name' => 'name']
            );

        return $collection;
    }

    /**
     * @return false|string|null
     * @throws LocalizedException
     */
    public function checkIfHtmlToImageInstalled()
    {
        $disabled = explode(',', ini_get('disable_functions'));
        if (in_array('shell_exec', $disabled)) {
            return false;
        }

        if (null === $this->_checkIfHtmlToImageInstalledResult) {
            $cacheKeyName = $this->getHtmlToImageCacheKeyName();
            /** Catch  exception if wkhtmltoimage is missing. */
            try {
                $which = $this->shell->execute('which wkhtmltoimage');
            } catch (LocalizedException $e) {
                $which = '';
            }

            if ($which) {
                $this->_checkIfHtmlToImageInstalledResult = trim($which);
            } else {
                $path = $this->_cache->load($cacheKeyName);
                if ($path) {
                    $this->_checkIfHtmlToImageInstalledResult = $this->shell->execute(
                        "find $path -name \"wkhtmltoimage\""
                    );
                    if ($this->_checkIfHtmlToImageInstalledResult) {
                        $this->_checkIfHtmlToImageInstalledResult = 'wkhtmltoimage';
                    }

                    if (!$this->_checkIfHtmlToImageInstalledResult) {
                        // moved or deleted
                        $this->_cache->remove($cacheKeyName);
                        $this->_messageManager->addWarningMessage(
                            'The wkhtmltoimage thumbnail generation tool is missing.
                            Newsletter popup thumbnail generation is now disabled.
                            Please contact your webserver admin to install wkhtmltoimage command line tool.'
                        );
                    }
                }
            }
        }

        return $this->_checkIfHtmlToImageInstalledResult;
    }

    public function getHtmlToImageCacheKeyName()
    {
        return 'prnewsletter_popup_htmltoimage';
    }

    public function getFrontendUrl($url, $params = [], $checkDomain = false)
    {
        $result = null;
        $params = array_merge(['key' => null, '_nosid' => true], $params);

        $websites = $this->_storeManager->getWebsites(true);
        foreach ($websites as $website) {
            $storeId = $website
                ->getDefaultGroup()
                ->getDefaultStoreId();

            $result = $this->_storeManager->getStore($storeId)->getUrl($url, $params);

            if (!$checkDomain || ($checkDomain && parse_url($this->_storeManager->getStore()->getBaseUrl(), PHP_URL_HOST) == parse_url($result, PHP_URL_HOST))) {
                break;
            }
        }

        if ($result) {
            $result = str_replace(
                $this->backendHelper->getAreaFrontName() . '/',
                '',
                $result
            );

            if (false !== ($length = stripos($result, '?'))) {
                $result = substr($result, 0, $length);
            }

            if ($this->configUtils->getStoreConfig('web/seo/use_rewrites')) {
                $result = str_replace('index.php/', '', $result);
            }
        }

        return $result;
    }

    public function getBaseScreenUrl($obj, $useParentBase = false)
    {
        if ($useParentBase) {
            if ($obj->getBaseTemplateId() == 0) {
                return false;
            }

            if (!$name = $obj->getBaseTemplateName()) {
                return false;
            }
        } else {
            if ($obj->getBaseTemplateId() != -1) {
                return false;
            }

            if (!$name = $obj->getTemplateName()) {
                $name = $obj->getName();
            }
        }

        $name = str_replace(['.', ' '], ['', '_'], $name);
        return $this->_viewRepository->getUrl(
            'Plumrocket_Newsletterpopup::images/screens/' . strtolower($name) . '.jpg'
        );
    }

    public function getScreenUrl($item)
    {
        if ($screenUrl = $this->getBaseScreenUrl($item)) {
            return $screenUrl;
        }

        $filePath = $item->getThumbnailFilePath();
        $previewPath = $item->getThumbnailCacheFilePath(true);

        if (!file_exists($filePath)) {
            $item->generateThumbnail();
            $previewPath = false;
        }

        if (file_exists($filePath)) {
            $cachedFilePath = $item->getThumbnailCacheFilePath();
            if (!$previewPath || !file_exists($cachedFilePath)) {
                $previewPath = $this->_imageHelper->resize(
                    'prnewsletterpopup/' . ($item->getIsTemplate()? 'popup_template_' : 'popup_') . $item->getId() . '.png',
                    self::THUMBNAIL_WIDTH
                );
            }
        } else {
            $previewPath = false;
        }

        if (!$previewPath) {
            $previewPath = $this->getBaseScreenUrl($item, true);
        }

        if (!$previewPath) {
            $previewPath = $this->_viewRepository->getUrl('Plumrocket_Newsletterpopup::images/none.jpg');
        }

        return $previewPath;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @param string                                              $defaultNote
     * @return string
     */
    public function getNoteForDisabledByTypeField(PopupInterface $popup, string $defaultNote = ''): string
    {
        return !$popup->isModal() ? 'Not available for type "Widget Template"' : $defaultNote;
    }
}
