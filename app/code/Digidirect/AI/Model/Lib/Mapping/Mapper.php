<?php
namespace Digidirect\AI\Model\Lib\Mapping;

class Mapper implements MapperInterface
{
    /**
     * @var array
     */
    protected $keysMap;

    /**
     * @var array
     */
    protected $valuesMap;

    /**
     * @var array
     */
    protected $callbacks;

    /**
     * Mapper constructor.
     * @param array $keysMap
     * @param array $valuesMap
     * @param array $callbacks
     */
    public function __construct(
        array $keysMap = [],
        array $valuesMap = [],
        array $callbacks = []
    ) {
        $this->keysMap = $keysMap;
        $this->valuesMap = $valuesMap;
        $this->callbacks = $callbacks;
    }

    /**
     * @param array $data
     * @param array $parentKeys
     * @return array
     */
    public function map(array $data, $parentKeys = [])
    {
        $newData = [];
        foreach ($data as $key => $value) {
            $newKey = $this->_mapKey($key, $parentKeys, $data);
            $parentKey = array_merge($parentKeys, [$this->generateKey($key)]);
            if (is_array($value)) {
                $newData[$newKey] = $this->map($value, $parentKey);
            } else {
                $newData[$newKey] = $this->_mapValue($value, $parentKey, $data);
            }
        }

        if (empty($parentKeys)) {
            $newData = $this->callCallbacks($newData);
        }

        return $newData;
    }

    /**
     * @param mixed $value
     * @param array $path
     * @param array $data
     * @return mixed
     */
    protected function _mapKey($value, array $path, $data)
    {
        return $this->_mapData($value, $path, $this->keysMap, $data);
    }

    /**
     * @param mixed $value
     * @param array $path
     * @param array $data
     * @return mixed
     */
    protected function _mapValue($value, array $path, $data)
    {
        $value = $this->_mapData($value, $path, $this->valuesMap, $data);
        return $this->_mapData($value, $path, $this->valuesMap, $data, false);
    }

    /**
     * @param mixed $value
     * @param string $path
     * @param array $map
     * @param array $data
     * @param bool $includeValue
     * @return mixed
     */
    public function _mapData($value, $path, $map, $data, $includeValue = true)
    {
        if ($includeValue && !is_object($value)) {
            $path = array_merge($path, [$value]);
        }
        $pathInMap = implode('.', $path);
        if (array_key_exists($pathInMap, $map)) {
            return $this->generateValue($map[$pathInMap], func_get_args());
        }
        return $value;
    }

    /**
     * @param string|number $key
     * @return string
     */
    protected function generateKey($key)
    {
        if (is_numeric($key)) {
            $key = '*';
        }
        return $key;
    }

    /**
     * @param mixed $value
     * @param mixed $callbackData
     * @return mixed
     */
    protected function generateValue($value, $callbackData = [])
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
     * @param array $data
     * @return array
     */
    protected function callCallbacks(array $data)
    {
        foreach ($this->callbacks as $callback) {
            $data = $this->generateValue($callback, [$data]);
        }
        return $data;
    }
}
