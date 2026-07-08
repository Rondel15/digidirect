<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Plugin;

use Magento\Framework\Filter\Template;

class FilterDirectives
{
    /**
     * @var bool
     */
    private $isEnabledFlag;

    /**
     * Disallow delete newsletter directives.
     *
     * @param Template $subject
     * @param callable $proceed
     * @param string   $value
     * @return string
     */
    public function aroundFilter(Template $subject, callable $proceed, $value)
    {
        if (! $this->isEnabledFlag) {
            return $proceed($value);
        }

        if (false !== strpos($value, '{{coupon_code}}')) {
            return $value;
        }

        return $proceed($value);
    }

    /**
     * Enable flag.
     *
     * @return void
     */
    public function enable(): void
    {
        $this->isEnabledFlag = true;
    }

    /**
     * Disable flag.
     *
     * @return void
     */
    public function disable(): void
    {
        $this->isEnabledFlag = false;
    }
}
