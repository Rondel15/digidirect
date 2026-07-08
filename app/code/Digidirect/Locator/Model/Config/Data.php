<?php

namespace Digidirect\Locator\Model\Config;

/**
 * Class Data
 * @package Digidirect\Locator\Model\Config
 */
class Data extends \Magento\Framework\Config\Data
{
    const PROCESSOR_INTERFACE = 'Digidirect\Locator\Model\ProcessorInterface';

    /**
     * Array of validated classes
     * @var array
     */
    protected $validatedClasses = [];

    /**
     * @param array|null $data
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getEntities(array $data = null)
    {
        $entities = $this->get('locator');
        $result = [];
        foreach ($entities as $key => $entity) {
            if (is_array($data) && !in_array($key, $data)) {
                continue;
            }
            if (!isset($this->validatedClasses[$entity['instance']])) {
                $obj = new \ReflectionClass($entity['instance']);
                $this->validatedClasses[$entity['instance']] = $obj->isSubclassOf(self::PROCESSOR_INTERFACE);
            }
            if (!$this->validatedClasses[$entity['instance']]) {
                throw new \Exception(
                    __(
                        '%1 is must be an instance of %2 ',
                        $entity['instance'],
                        self::PROCESSOR_INTERFACE
                    )
                );
            }
            $result[$key] = $entity;
        }
        return $result;
    }
}
