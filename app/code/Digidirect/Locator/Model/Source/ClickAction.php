<?php

namespace Digidirect\Locator\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Listing details click action
 *
 * @since 1.2.0
 */
class ClickAction implements ArrayInterface
{
    const ACTION_REDIRECT = 'redirect';
    const ACTION_OPEN_IN_POPUP = 'open_in_popup';

    /**
     * @var array
     */
    protected $actionsPool = [];

    /**
     * ClickAction constructor.
     * @param array $actionsPool
     */
    public function __construct(
        array $actionsPool = [self::ACTION_REDIRECT => 'Redirect user', self::ACTION_OPEN_IN_POPUP => 'Open in a pop-up']
    ) {
        $this->actionsPool = $actionsPool;
    }

    /**
     * Get all possible API
     *
     * @return array
     */
    public function toOptionArray()
    {
        $api = [];
        foreach ($this->actionsPool as $code => $label) {
            $api[] = [
                'value' => $code,
                'label' => __($label)
            ];
        }
        return $api;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $api = [];
        foreach ($this->actionsPool as $code => $label) {
            $api[$code] = __($label);
        }
        return $api;
    }
}
