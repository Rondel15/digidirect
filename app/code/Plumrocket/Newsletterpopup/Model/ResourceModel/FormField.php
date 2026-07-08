<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Plumrocket\Newsletterpopup\Api\Data\PopupFieldDataInterface;

class FormField extends AbstractDb
{
    const MAIN_TABLE_NAME = 'plumrocket_newsletterpopup_form_fields';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE_NAME, 'entity_id');
    }

    /**
     * @param int $popupId
     * @return string[]
     */
    public function getAllFieldsNames(int $popupId = 0): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::MAIN_TABLE_NAME),
            PopupFieldDataInterface::NAME
        );

        $select->where(PopupFieldDataInterface::POPUP_ID . ' = ?', $popupId);

        return $connection->fetchCol($select);
    }
}
