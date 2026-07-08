<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Setup\Recurring;

use Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Factory as BuildInThemeFactory;
use Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Registry as BuildInThemeRegistry;

class BuildInTheme
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Registry
     */
    private $themeRegistry;

    /**
     * @var BuildInThemeFactory
     */
    private $buildInThemeFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface
     */
    private $buildInThemeRepository;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Registry  $themeRegistry
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Factory   $buildInThemeFactory
     * @param \Plumrocket\Newsletterpopup\Api\BuildInThemeRepositoryInterface $buildInThemeRepository
     */
    public function __construct(
        BuildInThemeRegistry $themeRegistry,
        BuildInThemeFactory $buildInThemeFactory,
        BuildInThemeRepositoryInterface $buildInThemeRepository
    ) {
        $this->themeRegistry = $themeRegistry;
        $this->buildInThemeFactory = $buildInThemeFactory;
        $this->buildInThemeRepository = $buildInThemeRepository;
    }

    public function execute()
    {
        $existingThemes = $this->buildInThemeRepository->getList()->getItems();

        foreach ($this->themeRegistry->getList() as $themeData) {
            $existingTheme = $this->getThemeByIdentifier(
                $existingThemes,
                $themeData[PopupThemeInterface::IDENTIFIER]
            );

            if ($existingTheme) {
                $theme = $existingTheme->addData($themeData);
            } else {
                $theme = $this->buildInThemeFactory->create(['data' => $themeData]);
            }

            $this->buildInThemeRepository->save($theme);
        }
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface[] $existingThemes
     * @param string                                                     $identifier
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface|null
     */
    private function getThemeByIdentifier(array $existingThemes, string $identifier)
    {
        foreach ($existingThemes as $theme) {
            if ($theme->getIdentifier() === $identifier) {
                return $theme;
            }
        }
        return null;
    }
}
