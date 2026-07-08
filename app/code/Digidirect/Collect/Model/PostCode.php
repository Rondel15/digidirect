<?php

namespace Digidirect\Collect\Model;

use Magento\Framework\Model\AbstractModel;
use Digidirect\Collect\Api\Data\PostCodeInterface as postCodeInterface;

class PostCode extends AbstractModel implements postCodeInterface
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Collect\Model\ResourceModel\PostCode');
    }

    /**
     * Get Collect Place Logitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->getData(self::TABLE_COLUMN_LONGITUDE);
    }

    /**
     * Get Collect Place Latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->getData(self::TABLE_COLUMN_LATITUDE);
    }
}
