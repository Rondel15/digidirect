<?php

namespace Digidirect\FreeGift\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class InfoBuyRequestSerializer
 * @package Digidirect\FreeGift\Helper
 */
class InfoBuyRequestSerializer extends AbstractHelper
{
    /**
     * Needed fo compatibility with 2.1.8
     */
    const MAGENTO_VERSION_2_1_8 = '2.1.8';

    /**
     * Serializer interface instance.
     *
     * @var SerializerJson
     */
    protected $serializer;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Data constructor.
     * @param Context $context
     * @param SerializerJson $serializer
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        SerializerJson $serializer,
        ProductMetadataInterface $productMetadata
    ) {
        $this->serializer = $serializer;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @param mixed $data
     * @return bool|string
     */
    public function serialize($data)
    {
        /** Needed fo compatibility with 2.1.8 */
        if (version_compare($this->_checkVersion(), self::MAGENTO_VERSION_2_1_8, '=')) {
            return serialize($data);
        }

        return $this->serializer->serialize($data);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function unserialize($string)
    {
        /** Needed fo compatibility with 2.1.8 */
        if (version_compare($this->_checkVersion(), self::MAGENTO_VERSION_2_1_8, '=')) {
            return  unserialize($string);
        }
        return $this->serializer->unserialize($string);
    }

    /**
     * @return string
     */
    protected function _checkVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
