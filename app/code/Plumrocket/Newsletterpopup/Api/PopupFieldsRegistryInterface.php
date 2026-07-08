<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api;

/**
 * You can pass additional fields via DI
 * either by pass into constructor or using After plugin on getList method
 *
 * They'll be installed during recurring step of setup:upgrade
 *
 * @since v3.10.0
 */
interface PopupFieldsRegistryInterface
{
    /**
     * Format
     * [
     *      name => [
     *          'label' => string,
     *          'enable' => int, - 0,1
     *          'sort_order' => int,
     *          'popup_id' => int, - put 0 for all popups
     *      ]
     * ]
     *
     * @param array $fields
     */
    public function __construct(array $fields = []);

    /**
     * Get all fields.
     *
     * @return array[]
     */
    public function getList(): array;
}
