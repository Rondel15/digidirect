<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Content;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Module\Dir;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Psr\Log\LoggerInterface;

/**
 * Load html/css files content from data/theme/
 *
 * @since 4.0.0
 */
class Loader
{
    /**
     * @var string
     */
    private $themeDir;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $fileReaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $fileDriver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\Module\Dir                  $moduleDir
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReaderFactory
     * @param \Magento\Framework\Filesystem\DriverInterface  $fileDriver
     * @param \Psr\Log\LoggerInterface                       $logger
     */
    public function __construct(
        Dir $moduleDir,
        ReadFactory $fileReaderFactory,
        DriverInterface $fileDriver,
        LoggerInterface $logger
    ) {
        $moduleRootDir = $moduleDir->getDir('Plumrocket_Newsletterpopup');
        $this->themeDir = $moduleRootDir . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR
            . 'theme' . DIRECTORY_SEPARATOR;

        $this->fileReaderFactory = $fileReaderFactory;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
    }

    public function load(string $identifier): array
    {
        $extensions = [
            PopupThemeInterface::HTML => '.html',
            PopupThemeInterface::CSS => '.css',
            PopupThemeInterface::DEFAULT_CONFIGURATION => '.json',
        ];

        $result = [];
        foreach ($extensions as $key => $extension) {
            $filePath = $this->themeDir . $identifier . $extension;
            try {
                $content = $this->fileReaderFactory->create($filePath, $this->fileDriver)->readAll();
            } catch (FileSystemException $fileSystemException) {
                $this->logger->warning($fileSystemException->getMessage());
                $content = '';
            }

            $result[$key] = $content;
        }

        return $result;
    }
}
