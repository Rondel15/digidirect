<?php
namespace Digidirect\AI\Model\Lib\CustomRule;

interface CustomRuleInterface
{
    /**
     * @param array $data
     * @return array|false
     */
    public function apply(array $data);
}
