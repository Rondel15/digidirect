<?php

namespace Digidirect\Blog\Ui\DataProvider;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class AbstractFormDataProvider
 */
class AbstractFormDataProvider extends AbstractDataProvider
{
    const FORM_COMPONENT = '';

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var array|[]
     */
    protected $fieldsetConfiguration;

    /**
     * AbstractFormDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     * @param array $fieldsetConfiguration
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PoolInterface $pool,
        array $meta = [],
        array $data = [],
        array $fieldsetConfiguration = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pool = $pool;
        $this->fieldsetConfiguration = $fieldsetConfiguration;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function extractData(array $data)
    {
        $newData = [];
        if (!empty($this->fieldsetConfiguration)) {
            foreach ($this->fieldsetConfiguration as $fieldsetCode => $fieldsetFields) {
                foreach ($fieldsetFields as $fieldsetField) {
                    if (isset($data[$fieldsetField])) {
                        $newData[$fieldsetCode][$fieldsetField] = $data[$fieldsetField];
                    }
                }
            }

            foreach ($data as $fieldCode => $fieldValue) {
                if (isset($newData[$fieldCode])) {
                    continue;
                }

                $newData[$fieldCode] = $fieldValue;
            }
        }

        return !empty($newData) ? $newData : $data;
    }

    /**
     * Modify Meta
     *
     * @return array
     */
    public function getMeta()
    {
        $metaOriginal = parent::getMeta();
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($metaOriginal);
            $metaOriginal = array_replace_recursive($metaOriginal, $meta);
        }
        return $metaOriginal;
    }
}
