<?php

namespace Digidirect\FreeGift\Plugin\Magento\Catalog\Helper\Product;

use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Configuration
 * @package Digidirect\FreeGift\Plugin\Magento\Catalog\Helper\Product
 */
class Configuration
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
    protected $_serializer;

    /**
     * @var ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * Configuration constructor.
     * @param SerializerJson $serializer
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        SerializerJson $serializer,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_serializer = $serializer;
        $this->_productMetadata = $productMetadata;
    }

    /**
     * Retrieves product configuration options
     *
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param \Closure $procede
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCustomOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $procede,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        /** Needed fo compatibility with 2.1.8 */
        if (version_compare($this->_productMetadata->getVersion(), static::MAGENTO_VERSION_2_1_8, '=')) {
            $product = $item->getProduct();
            $options = [];
            $optionIds = $item->getOptionByCode('option_ids');
            if ($optionIds) {
                $options = [];
                foreach (explode(',', $optionIds->getValue()) as $optionId) {
                    $option = $product->getOptionById($optionId);
                    if ($option) {
                        $itemOption = $item->getOptionByCode('option_' . $option->getId());
                        /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                        $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setConfigurationItem($item)
                            ->setConfigurationItemOption($itemOption);

                        if ('file' == $option->getType()) {
                            $downloadParams = $item->getFileDownloadParams();
                            if ($downloadParams) {
                                $url = $downloadParams->getUrl();
                                if ($url) {
                                    $group->setCustomOptionDownloadUrl($url);
                                }
                                $urlParams = $downloadParams->getUrlParams();
                                if ($urlParams) {
                                    $group->setCustomOptionUrlParams($urlParams);
                                }
                            }
                        }

                        $options[] = [
                            'label' => $option->getTitle(),
                            'value' => $group->getFormattedOptionValue($itemOption->getValue()),
                            'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
                            'option_id' => $option->getId(),
                            'option_type' => $option->getType(),
                            'custom_view' => $group->isCustomizedView(),
                        ];
                    }
                }
            }

            $addOptions = $item->getOptionByCode('additional_options');
            if ($addOptions) {
                $options = array_merge($options, $this->_serializer->unserialize($addOptions->getValue()));
            }
            return $options;
        }
        return $procede($item);
    }
}
