<?php
namespace Digidirect\AI\Model\Lib\Mapping;

interface MapperInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function map(array $data);
}
