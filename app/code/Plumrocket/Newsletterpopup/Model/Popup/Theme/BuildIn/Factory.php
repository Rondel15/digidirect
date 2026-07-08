<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Theme\BuildIn;

use Plumrocket\Newsletterpopup\Model\Popup\Theme;
use Plumrocket\Newsletterpopup\Model\Popup\ThemeFactory;

/**
 * @since 4.0.0
 */
class Factory
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\ThemeFactory
     */
    private $themeFactory;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup\ThemeFactory $themeFactory
     */
    public function __construct(ThemeFactory $themeFactory)
    {
        $this->themeFactory = $themeFactory;
    }

    /**
     * @param array $data
     * @return \Plumrocket\Newsletterpopup\Model\Popup\Theme
     */
    public function create(array $data): Theme
    {
        /** @var \Plumrocket\Newsletterpopup\Model\Popup\Theme $theme */
        $theme = $this->themeFactory->create($data);
        $theme->setData('base_template_id', -1);
        return $theme;
    }
}
