<?php
namespace Digidirect\Utilities\Plugin\ConfigurableProduct\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableNative;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Configurable
 * @package Digidirect\Utilities\Plugin\ConfigurableProduct\Block\Product\View\Type
 */
class Configurable
{
    /**
     * Json Decoder
     *
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Json Encoder
     *
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * Configurable constructor.
     *
     * @param DecoderInterface $jsonDecoder
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(DecoderInterface $jsonDecoder, EncoderInterface $jsonEncoder)
    {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Add custom attributes values
     *
     * @param ConfigurableNative $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(
        ConfigurableNative $subject,
        $result
    ) {
        $attributes = $subject->getAdditionalAttributes();
        if (is_array($attributes) && !empty($attributes)) {
            $jsonConfig = $this->jsonDecoder->decode($result);
            $jsonConfig['additionalAttributes'] = $jsonConfig['additionalAttributesLabels'] = [];

            $allowedProducts = $subject->getAllowProducts();
            foreach ($allowedProducts as $product) {
                $productId = $product->getId();
                foreach ($attributes as $attributeCode => $attributeLabel) {
                    $jsonConfig['additionalAttributes'][$productId][$attributeCode] = $product->getData($attributeCode);
                    $jsonConfig['additionalAttributesLabels'][$attributeCode] = $attributeLabel;
                }
            }

            return $this->jsonEncoder->encode($jsonConfig);
        }

        return $result;
    }
}
