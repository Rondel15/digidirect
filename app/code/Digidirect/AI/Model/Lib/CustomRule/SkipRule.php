<?php
namespace Digidirect\AI\Model\Lib\CustomRule;

class SkipRule implements CustomRuleInterface
{
    const LT = 'lt';
    const LTE = 'lte';
    const EQ = 'eq';
    const NOT_EQ = 'notEq';
    const GTE = 'gte';
    const GT = 'gt';
    const IN = 'in';
    const NOT_IN = 'notIn';

    const CONDITION = 'condition';
    const VALUE = 'value';

    /**
     * @var array
     */
    protected $rules;

    /**
     * SkipRule constructor.
     *
     * @param array $rules
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @param array $data
     * @param mixed $initiator
     * @return array
     */
    public function apply(array $data, $initiator = null)
    {
        foreach ($this->rules as $fieldName => $rule) {
            $arrayFieldName = explode('.', $fieldName);
            $data = $this->filterData($data, $arrayFieldName, $rule);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $arrayFieldName
     * @param array $rule
     * @param int $depth
     * @return array
     */
    public function filterData($data, $arrayFieldName, $rule, $depth = 0)
    {
        foreach ($data as $dataKey => $row) {
            $currentField = $arrayFieldName[$depth];
            if (!isset($row[$currentField])) {
                continue;
            }
            if (is_array($row[$currentField])) {
                $data[$dataKey][$currentField] = $this->filterData(
                    $row[$currentField],
                    $arrayFieldName,
                    $rule,
                    $depth + 1
                );
                continue;
            }
            if ($this->doComparison(
                $row[$arrayFieldName[$depth]],
                $rule[self::CONDITION] ?? self::EQ,
                $rule[self::VALUE] ?? $rule
            )) {
                unset($data[$dataKey]);
            }
        }

        return $data;
    }

    /**
     * @param mixed $a
     * @param string $operator
     * @param mixed $b
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD)
     */
    protected function doComparison($a, $operator, $b)
    {
        $method = 'operator' . $operator;
        if (!method_exists($this, $method)) {
            throw new \Exception("The {$operator} operator does not exists");
        }

        return $this->$method($a, $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorLt($a, $b)
    {
        return ($a < $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorLte($a, $b)
    {
        return ($a <= $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorEq($a, $b)
    {
        return ($a === $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorNotEq($a, $b)
    {
        return ($a !== $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorGte($a, $b)
    {
        return ($a >= $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorGt($a, $b)
    {
        return ($a > $b);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorIn($a, $b)
    {
        return (in_array($a, $b, true));
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    protected function operatorNotIn($a, $b)
    {
        return (!in_array($a, $b, true));
    }
}
