<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleRepositoryInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule as ExtendedCatalogRuleResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ExtendedCatalogRuleRepository
 * @package Digidirect\ExtendedCatalogPriceRule\Model
 */
class ExtendedCatalogRuleRepository implements ExtendedCatalogRuleRepositoryInterface
{
    /**
     * @var ExtendedCatalogRuleResource
     */
    protected $ruleResource;

    /**
     * @var ExtendedCatalogRuleFactory
     */
    protected $ruleFactory;

    /**
     * ExtendedCatalogRuleRepository constructor.
     * @param ExtendedCatalogRuleResource $ruleResource
     * @param ExtendedCatalogRuleFactory $ruleFactory
     */
    public function __construct(
        ExtendedCatalogRuleResource $ruleResource,
        ExtendedCatalogRuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param int $id Extended Catalog Rule entity ID
     * @return ExtendedCatalogRuleInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $extendedRule = $this->ruleFactory->create();
        $this->ruleResource->load($extendedRule, $id);

        if (!$extendedRule->getId()) {
            throw new NoSuchEntityException(
                __('Extended Catalog Price Rule with id "%1" does not exist.', $id)
            );
        }
        return $extendedRule;
    }

    /**
     * @param ExtendedCatalogRuleInterface $rule
     * @return ExtendedCatalogRuleInterface
     * @throws CouldNotSaveException
     */
    public function save(ExtendedCatalogRuleInterface $rule)
    {
        try {
            $this->ruleResource->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Extended Catalog Price Rule with id "%1" could not be saved.', $rule->getId())
            );
        }
        return $rule;
    }

    /**
     * @param ExtendedCatalogRuleInterface $rule
     * @return ExtendedCatalogRuleResource
     * @throws \Exception
     */
    public function delete(ExtendedCatalogRuleInterface $rule)
    {
        return $this->ruleResource->delete($rule);
    }
}
