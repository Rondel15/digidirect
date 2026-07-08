<?php
namespace Digidirect\AI\Plugin\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Validator as Subject;

class Validator
{
    const ILLEGAL_CHARACTER_SEARCH = 'Detected an illegal character in input string';

    /**
     * @var Product
     */
    protected $product;

    /**
     * Validator constructor.
     *
     * @param Product $product
     */
    public function __construct(
        Product $product
    ) {
        $this->product = $product;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param array ... $params
     * @return bool
     * @throws \Exception
     */
    public function aroundIsAttributeValid(Subject $subject, callable $proceed, ... $params)
    {
        try {
            $result = $proceed(...$params);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), self::ILLEGAL_CHARACTER_SEARCH) !== false) {
                $this->product->addRowError(
                    __(
                        'Attribute "%1" from Product with sku = "%2" contains illegal character',
                        $params[0],
                        $params[2]['sku'] ?? 'sku not found'
                    ),
                    0
                );
                return false;
            }
            throw $e;
        }
        return $result;
    }
}
