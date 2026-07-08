<?php
namespace Digidirect\AI\Model\Integrations\Rule;

use Digidirect\AI\Model\Integrations\Rule\Mapping\MapperInterface;

abstract class RuleAbstract implements RuleInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var MapperInterface
     */
    protected $_mapper;

    /**
     * RuleAbstract constructor.
     * @param MapperInterface|null $mapper
     * @param null $description
     */
    public function __construct(
        MapperInterface $mapper = null,
        $description = null
    ) {
        $this->_mapper = $mapper;
        $this->description = $description;
    }

    /**
     * @param mixed $entity
     * @return bool
     */
    public function apply($entity)
    {
        if ($this->isEntitySatisfied($entity)) {
            return $this->process($entity);
        }
        return false;
    }
}
