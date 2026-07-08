<?php

namespace Digidirect\AbstractGiftCard\Model;

use \Digidirect\AbstractGiftCard\Model\Service\AbstractService;

/**
 * service class.
 */
class GiftCardService extends AbstractService implements \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterface
{
    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_title;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var bool
     */
    protected $_isActive;

    /**
     * @param string $code
     * @param string $title
     * @param int $storeId
     * @param bool $isActive
     */
    public function __construct($code, $title, $storeId, $isActive)
    {
        $this->_code = $code;
        $this->_title = $title;
        $this->_storeId = $storeId;
        $this->_isActive = $isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($storeId = null)
    {
        return $this->_isActive;
    }
}
