<?php
/**
 * @package     Plumrocket_NewsletterPopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * @since 4.3.0
 */
class AddThemeIdentifiers implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\PopupBuildInThemeRegistryInterface
     */
    private $buildInThemeRegistry;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface
     */
    private $buildInThemeRepository;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Plumrocket\Newsletterpopup\Api\PopupBuildInThemeRegistryInterface $popupThemeRegistry
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory $themeCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface $buildInThemeRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Plumrocket\Newsletterpopup\Api\PopupBuildInThemeRegistryInterface $popupThemeRegistry,
        \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\CollectionFactory $themeCollectionFactory,
        \Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface $buildInThemeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->buildInThemeRegistry = $popupThemeRegistry;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->buildInThemeRepository = $buildInThemeRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme\Collection $themeCollection */
        $themeCollection = $this->themeCollectionFactory->create();
        $themeCollection->addBuildInFilter();

        foreach ($this->buildInThemeRepository->getList()->getItems() as $theme) {
            if ($theme->getIdentifier()) {
                continue;
            }

            if ($identifier = $this->buildInThemeRegistry->getIdentifierForOldTheme((int) $theme->getId())) {
                $theme->setIdentifier($identifier);
                $this->buildInThemeRepository->save($theme);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
