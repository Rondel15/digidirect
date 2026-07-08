<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\Newsletterpopup\Model\Popup\Theme;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme as ThemeResource;

/**
 * @since 4.0.0
 * @method Theme[]    getItems()
 * @method Theme|null getItemByColumnValue($column, $value)
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            Theme::class,
            ThemeResource::class
        );
    }

    public function addBuildInFilter(): Collection
    {
        return $this->addFieldToFilter('base_template_id', -1);
    }

    public function addFieldToFilter($field, $alias = null)
    {
        if ($field === 'template_type') {
            $field = 'base_template_id';
            if (isset($alias['eq']) && $alias['eq'] !== -1) {
                $alias = [
                    'neq' => -1
                ];
            }
        }

        return parent::addFieldToFilter($field, $alias);
    }
}
