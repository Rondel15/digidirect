<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

/**
 * Class SubscriptionMode
 */
class SubscriptionMode extends \Plumrocket\Newsletterpopup\Model\Config\Source\Base
{
    /**
     * Define options key
     */
    const ALL_LIST = 'all';
    const ALL_SELECTED_LIST = 'all_selected';
    const ONE_LIST_RADIO = 'one_radio';
    const ONE_LIST_SELECT = 'one_select';
    const MUPTIPLE_LIST = 'multiple';

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return [
            self::ALL_LIST => __('Automatically subscribe to all lists (hide selector)'),
            self::ALL_SELECTED_LIST => __('Automatically subscribe to selected lists (hide selection in frontend)'),
            self::ONE_LIST_RADIO => __('Let user choose one list (radio-buttons)'),
            self::ONE_LIST_SELECT => __('Let user choose one list (selectbox)'),
            self::MUPTIPLE_LIST => __('Let user choose multiple lists (checkboxes)'),
        ];
    }
}
