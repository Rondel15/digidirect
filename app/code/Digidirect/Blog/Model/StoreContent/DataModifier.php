<?php

namespace Digidirect\Blog\Model\StoreContent;

/**
 * Reusable replacer for collections
 *
 * @since 2.0.0
 */
class DataModifier
{
    /**
     * @var array
     */
    protected $keyReplace = [];

    /**
     * @var array
     */
    protected $strPos = [];

    /**
     * @param array $arrayToModify
     * @param string $prefix
     * @return array
     */
    public function modifyData(array $arrayToModify, string $prefix)
    {
        foreach ($arrayToModify as $key => $value) {
            if (!isset($this->strPos[$key])) {
                $this->strPos[$key] = \strpos($key, $prefix);
            }
            if ($this->strPos[$key] !== false) {
                if (!isset($this->keyReplace[$key])) {
                    $this->keyReplace[$key] = \str_replace($prefix, '', $key);
                }
                $replaceKey = $this->keyReplace[$key];
                if (!isset($arrayToModify[$replaceKey])) {
                    $arrayToModify[$replaceKey] = $value;
                }
            }
        }
        return $arrayToModify;
    }
}
