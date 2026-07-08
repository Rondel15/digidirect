<?php

namespace Digidirect\Catalog\Helper\Product;

class Configuration extends \Magento\Catalog\Helper\Product\Configuration
{
    public function getCustomOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
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

                    $optionValues = $option->getValues() ? $option->getValues() : [];
                    $optionValuesArray = [];

                    foreach ($optionValues as $optionValue) {
                        $optionValuesArray[$optionValue->getOptionTypeId()] =  [
                            'price' => $optionValue->getPrice()
                        ];
                    }

                    $currentOptionID = $itemOption->getValue();
                    $option_price = $optionValuesArray[$currentOptionID]["price"];
                    $option_text = $group->getFormattedOptionValue($itemOption->getValue()) . " - $" . number_format($option_price, 2);

                    $options[] = [
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($itemOption->getValue()),
                        'option_price' => "$" . number_format($option_price, 2),
                        'option_text' => $option_text,
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
            $options = array_merge($options, $this->serializer->unserialize($addOptions->getValue()));
        }

        return $options;

    }
}

