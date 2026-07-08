<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Field;

use Plumrocket\Newsletterpopup\Api\PopupFieldsRegistryInterface;

/**
 * @since v3.10.0
 */
class Registry implements PopupFieldsRegistryInterface
{
    /**
     * @var array[]
     */
    private $fields;

    /**
     * AdditionalLocationRegistry constructor.
     *
     * @param array[] $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @return array[]
     */
    public function getList(): array
    {
        return $this->fields;
    }
}
