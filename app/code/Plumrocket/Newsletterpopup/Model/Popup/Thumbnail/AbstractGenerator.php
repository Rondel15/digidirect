<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Thumbnail;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

/**
 * @since 4.0.0
 */
abstract class AbstractGenerator
{
    const TYPE = '';

    /**
     * @var string
     */
    protected $thumbnailPath = '/prnewsletterpopup/';

    /**
     * @var string
     */
    protected $cacheSubDirectory = 'cache/' . Adminhtml::THUMBNAIL_WIDTH . 'x' . Adminhtml::THUMBNAIL_WIDTH . '/';

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Adminhtml
     */
    protected $adminhtmlHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SerializerInterface
     */
    protected $phpSerializer;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    private $shell;

    /**
     * @param \Plumrocket\Newsletterpopup\Helper\Adminhtml     $adminhtmlHelper
     * @param \Magento\Framework\Filesystem                    $filesystem
     * @param \Magento\Store\Model\Store                       $store
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager
     * @param \Magento\Framework\Serialize\SerializerInterface $phpSerializer
     * @param \Magento\Framework\ShellInterface                $shell
     */
    public function __construct(
        Adminhtml $adminhtmlHelper,
        Filesystem $filesystem,
        Store $store,
        ManagerInterface $messageManager,
        SerializerInterface $phpSerializer,
        ShellInterface $shell
    ) {
        $this->adminhtmlHelper = $adminhtmlHelper;
        $this->filesystem = $filesystem;
        $this->_store = $store;
        $this->messageManager = $messageManager;
        $this->phpSerializer = $phpSerializer;
        $this->shell = $shell;
    }

    public function generate(int $entityId): bool
    {
        if ($command = $this->adminhtmlHelper->checkIfHtmlToImageInstalled()) {
            $dirPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('prnewsletterpopup');
            if (! file_exists($dirPath) && ! mkdir($dirPath) && ! is_dir($dirPath)) {
                $this->messageManager->addErrorMessage(__('Directory was not created. Access denied.'));
                return false;
            }

            $params = ['id' => $entityId];
            if (static::TYPE === 'theme') {
                $params['is_template'] = 1;
            }

            $previewUrl = $this->adminhtmlHelper->getFrontendUrl('prnewsletterpopup/index/snapshot', $params);

            $filePath = $this->getImagePath($entityId);
            $cacheFilePath = $this->getImageCachePath($entityId);

            if (file_exists($cacheFilePath)) {
                unlink($cacheFilePath);
            }

            try {
                $this->shell->execute("$command --crop-w 800 $previewUrl $filePath 2>&1");
            } catch (LocalizedException $e) {
                return false;
            }
        }
        return true;
    }

    abstract public function getImagePath(int $entityId, bool $forWeb = false): string;

    abstract public function getImageCachePath(int $entityId, bool $forWeb = false): string;

    protected function webOrDirFormat($formatAsWeb, $path): string
    {
        return $formatAsWeb
            ? $this->_store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path
            : $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path);
    }
}
