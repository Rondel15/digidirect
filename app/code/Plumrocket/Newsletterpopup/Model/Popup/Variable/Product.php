<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Variable;

/**
 * @since 4.0.0
 */
class Product
{
    /**
     * @var array
     */
    private $variables = [
        '{{product_template}}' => 'Template which contains Image, Price and Name for Product. ' .
            'The Product is either selected by Customer on Product Page or chosen in Display ' .
            'Settings by Admin -> Default Product SKU',
        '{{product_name}}' => 'Product name',
        '{{product_sku}}' => 'Product SKU',
        '{{product_small_image_url}}' => 'Small product image url',
        '{{product_image_url}}' => 'Product image url',
        '{{product_url}}' => 'Product url',
        '{{product_price}}' => 'Product price (old price)',
        '{{product_sale_price}}' => 'Product price with discount from coupon',
        '{{product_discount_amount}}' => 'Product discount in currency',
        '{{product_discount_percent}}' => 'Product discount in percents',
        '{{product_formatted_sale}}' => 'Dynamic product discount ' .
            '(currency or percent depending on shopping cart rule)',
    ];

    /**
     * Variable constructor.
     *
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    /**
     * @return array|string[]
     */
    public function getList(): array
    {
        return $this->variables;
    }

    /**
     * @return array
     */
    public function getVariablesCode(): array
    {
        return array_keys($this->getList());
    }
}
