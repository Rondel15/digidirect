<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel;

class MailchimpList extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('plumrocket_newsletterpopup_mailchimp_list', 'entity_id');
    }

    /**
     * @param array $data
     * @return int
     */
    public function insertAndUpdateLists(array $data)
    {
        $updateColumns = [
            'popup_id',
            'integration_id',
            'name',
            'label',
            'enable',
            'sort_order',
        ];

        return $this->_getConnection('write')
            ->insertOnDuplicate(
                $this->getMainTable(),
                $data,
                $updateColumns
            );
    }
}
