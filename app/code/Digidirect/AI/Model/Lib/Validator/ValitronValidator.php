<?php
namespace Digidirect\AI\Model\Lib\Validator;

// @codingStandardsIgnoreFile

class ValitronValidator extends \Valitron\Validator
{
    /**
     * Run validations and return boolean result
     *
     * @return boolean
     */
    public function validate()
    {
        foreach ($this->_validations as $v) {
            foreach ($v['fields'] as $field) {
                list($values, $multiple) = $this->getPart($this->_fields, explode('.', $field));

                // Don't validate if the field is not required and the value is empty
                if ($this->hasRule('optional', $field) && isset($values)) {
                    //Continue with execution below if statement
                } elseif ($v['rule'] !== 'required'
                    && !$this->hasRule('required', $field)
                    && (! isset($values) || $values === '' || ($multiple && count($values) == 0))
                ) {
                    continue;
                }

                /** Digidirect improvements: */
                $keys = explode('.', $field);
                if ((count($keys) == 2 || count($keys) == 3 && $keys[1] == '*')
                    && $this->hasRule('optional', $keys[0])
                ) {
                    list($parentValues, $multiple) = $this->getPart($this->_fields, [$keys[0]]);
                    if (empty($parentValues)) {
                        continue;
                    }
                }
                /** End. */

                // Callback is user-specified or assumed method on class
                $errors = $this->getRules();
                if (isset($errors[$v['rule']])) {
                    $callback = $errors[$v['rule']];
                } else {
                    $callback = [$this, 'validate' . ucfirst($v['rule'])];
                }

                if (!$multiple) {
                    $values = [$values];
                }

                $result = true;
                foreach ($values as $value) {
                    $result = $result && call_user_func($callback, $field, $value, $v['params'], $this->_fields);
                }

                if (!$result) {
                    $this->error($field, $v['message'], $v['params']);
                }
            }
        }

        return count($this->errors()) === 0;
    }
}
