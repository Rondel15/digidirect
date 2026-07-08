<?php
namespace Digidirect\ProductOverlay\Model\ResourceModel\Rule\CatalogRule;

use Digidirect\ProductOverlay\Model\Rule\CatalogRuleOverlays;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements AttributeInterface
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
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
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
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();

        if (isset($entityData[CatalogRuleOverlays::OVERLAY_IDS])) {
            $overlayIds = $entityData[CatalogRuleOverlays::OVERLAY_IDS];
            if (!is_array($overlayIds)) {
                $overlayIds = array_filter(explode(',', (string)$overlayIds));
            }

            $entityType = CatalogRuleOverlays::OVERLAY_ENTITY_TYPE;
            if (!empty($overlayIds)) {
                $this->ruleResource->bindRuleToEntity($entityData[$linkField], $overlayIds, $entityType);
            } else {
                $oldIds = $this->ruleResource->getAssociatedEntityIds($entityData[$linkField], $entityType);
                if (!empty($oldIds)) {
                    $this->ruleResource->unbindRuleFromEntity($entityData[$linkField], $oldIds, $entityType);
                }
            }
        }
        return $entityData;
    }
}
