<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

use Plumrocket\Base\Model\OptionSource\AbstractSource;

class Cookies extends AbstractSource
{

    /**
     * @deprecated since 4.7.0
     * @see GLOBAL
     */
    public const _GLOBAL = self::GLOBAL;

    /**
     * @deprecated since 4.7.0
     * @see SEPARATE
     */
    public const _SEPARATE = self::SEPARATE;
    public const GLOBAL = 0;
    public const SEPARATE = 1;

    /**
     * @inheritdoc
     */
    public function toOptionHash(): array
    {
        return [
            self::GLOBAL   => __('Global cookie for all popups'),
            self::SEPARATE => __('Separate cookie for each popup'),
        ];
    }
}
