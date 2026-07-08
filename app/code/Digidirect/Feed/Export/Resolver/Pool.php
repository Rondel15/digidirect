<?php

namespace Digidirect\Feed\Export\Resolver;

class Pool
{
    /**
     * List of registered resolvers
     *
     * @var []
     */
    protected $resolvers;

    /**
     * Constructor
     *
     * @param [] $resolvers
     */
    public function __construct(
        $resolvers = []
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * Return resolver for object, based on object class and type (for products)
     *
     * @param object $object
     *
     * @return AbstractResolver|false
     */
    public function findResolver($object)
    {
        foreach ($this->resolvers as $resolver) {
            if ($object instanceof $resolver['for']) {
                if (!isset($resolver['type_id']) || $object->getData('type_id') == $resolver['type_id']) {
                    return $resolver['resolver'];
                }
            }
        }

        return false;
    }

    /**
     * List of registered resolvers
     *
     * @return AbstractResolver[]
     */
    public function getResolvers()
    {
        $list = [];

        foreach ($this->resolvers as $resolver) {
            $list[] = $resolver['resolver'];
        }

        return $list;
    }
}
