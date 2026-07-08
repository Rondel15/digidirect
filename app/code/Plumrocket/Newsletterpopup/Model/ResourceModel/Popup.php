<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Popup extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('plumrocket_newsletterpopup_popups', 'entity_id');
    }

    /**
     * Update popup views count without saving whole popup.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup $popup
     * @return void
     */
    public function updateViewsCount(\Plumrocket\Newsletterpopup\Model\Popup $popup): void
    {
        if (! empty($popup->getId())) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['views_count' => new \Zend_Db_Expr('views_count + 1')],
                'entity_id = '. $popup->getId()
            );
        }
    }

    /**
     * Update popup subscribers count without saving whole popup.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup $popup
     * @return void
     */
    public function updateSubscribersCount(\Plumrocket\Newsletterpopup\Model\Popup $popup): void
    {
        if (! empty($popup->getId())) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['subscribers_count' => new \Zend_Db_Expr('subscribers_count + 1')],
                'entity_id = '. $popup->getId()
            );
        }
    }
}
