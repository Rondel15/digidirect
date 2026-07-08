<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Popup;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @since 4.0.0
 */
class Theme extends AbstractDb
{
    const MAIN_TABLE_NAME = 'plumrocket_newsletterpopup_templates';
    const ID_FIELD_NAME = 'entity_id';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE_NAME, self::ID_FIELD_NAME);
    }

    protected function _afterDelete(AbstractModel $object)
    {
        $object->isDeleted(true);
        return $this;
    }

    public function useIsObjectNew($flag = true)
    {
        $this->_useIsObjectNew = (bool)$flag;
        return $this;
    }
}
