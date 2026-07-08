<?php
namespace Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule;

use Digidirect\ProductOverlay\Model\Rule\CatalogRuleOverlays;
use Digidirect\ProductOverlay\Api\OverlayRepositoryInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class DeleteHandler
 */
class DeleteHandler implements AttributeInterface
{
    /**
     * @var Rule
     */
    protected $ruleResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var OverlayRepositoryInterface
     */
    protected $overlayRepository;

    /**
     * @var CatalogRuleOverlays
     */
    protected $catalogRuleOverlays;

    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     * @param OverlayRepositoryInterface $overlayRepository
     * @param CatalogRuleOverlays $catalogRuleOverlays
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool,
        OverlayRepositoryInterface $overlayRepository,
        CatalogRuleOverlays $catalogRuleOverlays
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->overlayRepository = $overlayRepository;
        $this->catalogRuleOverlays = $catalogRuleOverlays;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        $idField = $this->metadataPool->getMetadata($entityType)->getIdentifierField();
        $ruleId = $entityData[$idField];
        $overlayIds = $this->catalogRuleOverlays->getOverlayIdsByCatalogRuleId($ruleId, true);

        foreach ($overlayIds as $overlayId) {
            $rulesCounter = [];
            $allCatalogRules = $this->catalogRuleOverlays->getCatalogRulesByOverlayId($overlayId, true);
            foreach ($allCatalogRules as $catalogRule) {
                $rulesCounter[$catalogRule['rule_id']] = $catalogRule;
            }

            if (count($rulesCounter) == 1) {
                $overlay = $this->overlayRepository->getById($overlayId);
                $overlay->setStatus(0);
                $this->overlayRepository->save($overlay);
            }
        }

        return $entityData;
    }
}
