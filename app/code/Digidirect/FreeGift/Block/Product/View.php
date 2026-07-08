<?php

namespace Digidirect\FreeGift\Block\Product;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;

class View extends AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param RuleRepositoryInterface $ruleRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleRepositoryInterface $ruleRepository,
        array $data = []
    ) {
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getProducts()
    {
        if ($this->_itemCollection === null) {
            $this->_itemCollection = $this->ruleRepository->getPromoItemsByProduct($this->getProduct());
        }
        return $this->_itemCollection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getProducts() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }

    /**
     * @return []
     */
    public function getProductRuleDescription()
    {
        return $this->ruleRepository->getRulesDescriptionsByProduct($this->getProduct());
    }
}
