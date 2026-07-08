<?php

namespace Digidirect\AI\Model\Lib\Mapping;

class MapperTemplateFilter implements MapperInterface
{
    const EXCLUDE_KEY_IF = 'excludeKeyIf';

    /**
     * @var TemplateFilter
     */
    protected $templateFilter;

    /**
     * @var array
     */
    protected $keysMap;

    /**
     * @var array
     */
    protected $callbacks;

    /**
     * @var array
     */
    protected $additionalVariables;

    /**
     * Mapper constructor.
     * @param TemplateFilter $templateFilter
     * @param array $keysMap
     * @param array $callbacks
     * @param array $additionalVariables
     */
    public function __construct(
        TemplateFilter $templateFilter,
        array $keysMap = [],
        array $callbacks = [],
        array $additionalVariables = []
    ) {
        $this->templateFilter = $templateFilter;
        $this->keysMap = $keysMap;
        $this->callbacks = $callbacks;
        $this->additionalVariables = $additionalVariables;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->templateFilter->setStoreId($storeId);
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->templateFilter->getStoreId();
    }

    /**
     * @param MapperInterface $mapper
     * @return array|null
     */
    protected function getByMapper($mapper)
    {
        if (!$mapper instanceof MapperInterface) {
            return null;
        }

        if ($mapper instanceof self) {
            $mapper->setStoreId($this->getStoreId());
        }

        return $mapper->map($this->templateFilter->getVariables());
    }

    /**
     * @param array $getter
     * @return mixed
     */
    protected function getByGetter($getter)
    {
        if (!is_array($getter)) {
            return null;
        }

        if (!isset($getter['class'], $getter['method'])) {
            return null;
        }

        $arguments = [];
        if (isset($getter['arguments']) && is_array($getter['arguments'])) {
            foreach ($getter['arguments'] as $argumentKey => $argumentTemplate) {
                $arguments[] = $this->templateFilter->filter($argumentTemplate);
            }
        }
        unset($getter['arguments']);
        return $this->getValueByClassMethod($getter, $arguments);
    }

    /**
     * @param string $variable
     * @return null|string
     */
    protected function getByVariable($variable)
    {
        if (is_string($variable) && $variable = trim($variable)) {
            $valueTemplate = sprintf('{{var %s}}', $variable);
            return $this->templateFilter->filter($valueTemplate);
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function getByValue($value)
    {
        if (is_array($value)) {
            return $this->mapKeyMap($value);
        }

        if (is_string($value)) {
            return $this->templateFilter->filter($value);
        }

        return $value;
    }

    /**
     * @param array $fieldData
     * @return mixed
     */
    protected function getByFieldData(array $fieldData)
    {
        if (isset($fieldData['getter'])) {
            $value = $this->getByGetter($fieldData['getter']);
        } elseif (isset($fieldData['arrayIterator'])) {
            $value = $this->getByArrayIterator($fieldData['arrayIterator']);
        } elseif (isset($fieldData['mapper'])) {
            $value = $this->getByMapper($fieldData['mapper']);
        } elseif (isset($fieldData['variable'])) {
            $value = $this->getByVariable($fieldData['variable']);
        } elseif (isset($fieldData['value'])) {
            $value = $this->getByValue($fieldData['value']);
        } else {
            $value = null;
        }
        if ($value === null || is_scalar($value)) {
            $valuesMap = $fieldData['valuesMap'] ?? [];
            if (is_array($valuesMap) && array_key_exists((string)$value, $valuesMap)) {
                $value = $valuesMap[$value];
            }
        }
        return $value;
    }

    /**
     * @param mixed $template
     * @return mixed
     */
    protected function wrapWithVarIfNotVar($template)
    {
        if (is_string($template) && !preg_match('/{{var\s(.*?)}}/si', $template)) {
            $template = sprintf('{{var %s}}', $template);
        }
        return $template;
    }

    /**
     * @param array $arrayIterator
     * @return array|null
     */
    protected function getByArrayIterator($arrayIterator)
    {
        $result = [];
        if (!is_array($arrayIterator) || empty($arrayIterator['iterator'])) {
            return null;
        }

        $iterator = $arrayIterator['iterator'];
        if (!is_string($iterator)) {
            return $result;
        }

        $iterator = $this->wrapWithVarIfNotVar($iterator);

        $array = $this->templateFilter->filter($iterator);
        if (!is_array($array) && (!$array instanceof \Traversable)) {
            return $result;
        }

        $backupVariables = $this->templateFilter->getVariables();

        $itemKey = $arrayIterator['itemKey'] ?? false;
        if (is_string($itemKey)) {
            $itemKey = $this->wrapWithVarIfNotVar($itemKey);
        }

        $itemVariableName = !empty($arrayIterator['itemVariableName']) ? $arrayIterator['itemVariableName'] : 'item';

        $fieldData = $arrayIterator;
        unset($fieldData['iterator'], $fieldData['itemKey'], $fieldData['itemVariableName']);

        foreach ($array as $key => $item) {
            if (is_string($itemKey)) {
                $key = $this->templateFilter->filter($itemKey);
            }
            $variables = array_merge($backupVariables, [$itemVariableName => $item]);
            $this->templateFilter->setVariables($variables);
            $value = $this->getByFieldData($fieldData);
            $result[$key] = $value;
        }

        $this->templateFilter->setVariables($backupVariables);

        if ($itemKey === true) {
            $result = array_values($result);
        }

        return $result;
    }

    /**
     * @param array $keysMap
     * @return array
     */
    protected function mapKeyMap($keysMap)
    {
        if (!is_array($keysMap)) {
            return null;
        }

        $result = [];
        foreach ($keysMap as $key => $fieldData) {
            if (is_string($fieldData)) {
                $fieldData = ['variable' => $fieldData];
            }
            if (is_array($fieldData)) {
                if (!empty($fieldData[self::EXCLUDE_KEY_IF])) {
                    $exclTemplate = $this->wrapWithVarIfNotVar($fieldData[self::EXCLUDE_KEY_IF]);
                    if ($this->getByValue($exclTemplate)) {
                        continue;
                    }
                }
                $key = $fieldData['key'] ?? $key;
                $value = $this->getByFieldData($fieldData);
            } else {
                $value = null;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param array $variables
     * @return array
     */
    public function map(array $variables)
    {
        $this->templateFilter->setVariables($variables);
        if ($this->additionalVariables) {
            foreach ($this->additionalVariables as $key => $template) {
                $this->templateFilter->addVariable($key, $this->templateFilter->filter($template));
            }
        }
        $result = $this->mapKeyMap($this->keysMap);
        $callbacksData = $this->callCallbacks($this->templateFilter->getVariables(), $result);
        $result = array_merge($result, $callbacksData);

        return $result;
    }

    /**
     * @param mixed $value
     * @param mixed $callbackData
     * @return mixed
     */
    protected function getValueByClassMethod($value, $callbackData = [])
    {
        if (is_array($value) && isset($value['class'], $value['method'])) {
            $value = array_values($value);
        }
        if (is_callable($value)) {
            return call_user_func_array($value, $callbackData);
        }
        return $value;
    }

    /**
     * @param array $variables
     * @param array $mappedData
     * @return array
     */
    protected function callCallbacks(array $variables, array $mappedData)
    {
        $result = [];
        foreach ($this->callbacks as $callback) {
            $callbackData = $this->getValueByClassMethod($callback, [$variables, $mappedData]);
            $result = array_merge($result, $callbackData);
        }
        return $result;
    }
}
