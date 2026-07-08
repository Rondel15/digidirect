<?php
namespace Digidirect\AI\Model\Integrations\Rule\Mapping;

interface MapperInterface
{
    /**
     * @return string
     */
    public function getMapperCode();

    /**
     * @return []
     */
    public function getMappingFields();

    /**
     * @param string $field
     * @return []
     */
    public function getFieldOptions($field);

    /**
     * @return mixed
     */
    public function getSavedData();
}
