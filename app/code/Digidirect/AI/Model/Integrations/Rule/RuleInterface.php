<?php
namespace Digidirect\AI\Model\Integrations\Rule;

interface RuleInterface
{
    /**
     * @param mixed $entity
     * @return bool
     */
    public function isEntitySatisfied($entity);

    /**
     * @param mixed $entity
     * @return mixed
     */
    public function process($entity);
}
