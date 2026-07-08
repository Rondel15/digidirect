<?php

namespace Digidirect\AbstractEntity\Model;

/**
 * Request data filter pool.
 */
class DataFilterPool
{
    /**
     * @var DataFilterInterface[]
     */
    protected $filters;

    /**
     * @param DataFilterInterface[] $filters [optional]
     */
    public function __construct(
        $filters = []
    ) {
        $this->filters = $filters;
    }

    /**
     * @param array $data
     * @return array
     */
    public function execute(array $data)
    {
        foreach ($this->filters as $dataFilter) {
            if (!$dataFilter instanceof \Digidirect\AbstractEntity\Model\DataFilterInterface) {
                throw new \InvalidArgumentException(__(
                    'Type %1 is not an instance of %2',
                    get_class($dataFilter),
                    \Digidirect\AbstractEntity\Model\DataFilterInterface::class
                ));
            }
            $dataFilter->execute($data);
        }
        return $data;
    }
}
