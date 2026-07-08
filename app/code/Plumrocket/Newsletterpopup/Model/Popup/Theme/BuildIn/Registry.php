<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn;

use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Api\PopupBuildInThemeRegistryInterface;
use Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Content\Loader;

/**
 * @since 4.0.0
 */
class Registry implements PopupBuildInThemeRegistryInterface
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var array|null
     */
    private $list;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Content\Loader
     */
    private $contentLoader;

    /**
     * @param \Magento\Framework\Serialize\SerializerInterface                     $serializer
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn\Content\Loader $contentLoader
     */
    public function __construct(
        SerializerInterface $serializer,
        Loader $contentLoader
    ) {
        $this->serializer = $serializer;
        $this->contentLoader = $contentLoader;
    }

    /**
     * @inheritDoc
     */
    public function getThemeData(string $identifier, bool $serialized = true): array
    {
        return $this->getList($serialized)[$identifier] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getList(bool $serialized = true): array
    {
        if (null === $this->list) {
            $themes = [
                'minimalist'           => '1. Minimalist',
                'emerald'              => '2. Emerald',
                'red_wine'             => '3. Red Wine',
                'strict_fashion'       => '4. Strict Fashion',
                'summertime'           => '5. Summertime',
                'glamour_blue'         => '6. Glamour Blue',
                'pink_style'           => '7. Pink Style',
                'nightlife'            => '8. Nightlife',
                'golden_black'         => '9. Golden Black',
                'adventure'            => '10. Adventure',
                'golden_diamond'       => '11. Golden Diamond',
                'fireworks'            => '12. Fireworks',
                'sticky_footer_bar'    => '13. Sticky Footer Bar',
                'sticky_footer_window' => '14. Sticky Footer Window',
                'giant'                => '15. Giant',
                'right_slide_out'      => '16. Right Slide Out',
                'hidden_treasure'      => '17. Hidden Treasure',
                'white_lotus'          => '18. White Lotus',
                'chocolate'            => '19. Chocolate',
                'default_theme'        => '20. Default Theme',
                'rectangles'           => '21. Rectangles',
                'orange_wall'          => '22. Orange Wall',
                'grey_edge'            => '23. Grey Edge',
                'amazing_shapes'       => '24. Amazing Shapes',
                'weltpixel'            => '25. Weltpixel',
                'cyber_monday'         => '26. Cyber Monday',
                'happy_halloween'      => '27. Happy Halloween',
                'valentines_day'       => '28. Happy Valentines Day',
                'valentines_day_2'     => '29. Happy Valentines Day 2',
                'mothers_day'          => '30. Happy Mothers Day',
                'full_screen'          => '31. Full Screen',
                'black_friday'         => '32. Black Friday',
            ];

            $this->list = [];
            foreach ($themes as $identifier => $name) {
                $content = $this->contentLoader->load($identifier);
                $this->list[$identifier] = [
                    PopupThemeInterface::IDENTIFIER            => $identifier,
                    PopupThemeInterface::NAME                  => $name,
                    PopupThemeInterface::HTML                  => $content[PopupThemeInterface::HTML],
                    PopupThemeInterface::CSS                   => $content[PopupThemeInterface::CSS],
                    PopupThemeInterface::DEFAULT_CONFIGURATION => $serialized
                        ? $content[PopupThemeInterface::DEFAULT_CONFIGURATION]
                        : $this->serializer->unserialize($content[PopupThemeInterface::DEFAULT_CONFIGURATION]),
                ];
            }
        }

        return $this->list;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifierForOldTheme(int $themeId): string
    {
        $idsMapping = [
            1 => 'minimalist',
            2 => 'emerald',
            3 => 'red_wine',
            4 => 'strict_fashion',
            5 => 'summertime',
            6 => 'glamour_blue',
            7 => 'pink_style',
            8 => 'nightlife',
            9 => 'golden_black',
            10 => 'adventure',
            11 => 'golden_diamond',
            12 => 'fireworks',
            13 => 'sticky_footer_bar',
            14 => 'sticky_footer_window',
            15 => 'giant',
            16 => 'right_slide_out',
            17 => 'hidden_treasure',
            18 => 'white_lotus',
            19 => 'chocolate',
            20 => 'default_theme',
        ];

        return $idsMapping[$themeId] ?? '';
    }
}
