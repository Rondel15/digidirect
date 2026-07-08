<?php
namespace Digidirect\AbstractEntity\Api\Data;

interface RelatedAttributesInterface
{
    /**
     * @param string $attributeName
     * @return AbstractEntityInterface|null
     */
    public function getAttributeObject($attributeName);

    /**
     * @param array $relatedAttributes
     * @return $this
     */
    public function loadRelatedAttributes(array $relatedAttributes);

    /**
     * @param string $attributeCode
     * @param string $glue
     * @return AbstractEntityInterface[]
     */
    public function getMultiSelectEntities($attributeCode, $glue = ',');
}
