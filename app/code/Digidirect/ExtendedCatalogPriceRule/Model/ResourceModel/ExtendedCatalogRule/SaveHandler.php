<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleManagementInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleRepositoryInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\ExtendedCatalogRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class SaveHandler
 * @package Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule
 */
class SaveHandler implements AttributeInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExtendedCatalogRuleRepositoryInterface
     */
    protected $extendedCatalogRuleRepository;

    /**
     * @var ExtendedCatalogRuleManagementInterface
     */
    protected $extendedCatalogRuleManagement;

    /**
     * @param MetadataPool $metadataPool
     * @param ExtendedCatalogRuleRepositoryInterface $extendedCatalogRuleRepository
     * @param ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement
     */
    public function __construct(
        MetadataPool $metadataPool,
        ExtendedCatalogRuleRepositoryInterface $extendedCatalogRuleRepository,
        ExtendedCatalogRuleManagementInterface $extendedCatalogRuleManagement
    ) {
        $this->metadataPool = $metadataPool;
        $this->extendedCatalogRuleRepository = $extendedCatalogRuleRepository;
        $this->extendedCatalogRuleManagement = $extendedCatalogRuleManagement;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        if ($entityData['simple_action'] !== RuleDisplayMessageInterface::ACTION_CODE) {
            return $entityData;
        }

        $idField = $this->metadataPool->getMetadata($entityType)->getIdentifierField();
        $entityId = $entityData[$idField];

        $plp = $entityData[RuleDisplayMessageInterface::PLP_LABEL];
        $pdp = $entityData[RuleDisplayMessageInterface::PDP_DESCRIPTION];
        $url = $entityData[RuleDisplayMessageInterface::URL_PROMOTION];

        /** @var ExtendedCatalogRule $extendedRule */
        $extendedRule = $this->extendedCatalogRuleManagement->loadByCatalogRuleId($entityId);
        $extendedRule->addData(
            [
                ExtendedCatalogRuleInterface::RULE_ID => $entityId,
                RuleDisplayMessageInterface::PLP_LABEL => $plp,
                RuleDisplayMessageInterface::PDP_DESCRIPTION => $pdp,
                RuleDisplayMessageInterface::URL_PROMOTION => $url
            ]
        );
        $this->extendedCatalogRuleRepository->save($extendedRule);

        return $entityData;
    }
}
