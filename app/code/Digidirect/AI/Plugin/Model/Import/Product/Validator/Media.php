<?php
namespace Digidirect\AI\Plugin\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\Validator\Media as Subject;

class Media extends Subject
{
    const URL_REGEXP = '#^(f|ht)tp(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$#i';

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param array $value
     * @return bool
     */
    public function aroundIsValid(Subject $subject, callable $proceed, $value)
    {
        $this->context = $subject->context;
        $this->_clearMessages();
        $valid = true;
        foreach ($this->mediaAttributes as $attribute) {
            if (isset($value[$attribute]) && strlen($value[$attribute])) {
                if (!$this->checkPath($value[$attribute]) && !$this->checkValidUrl($value[$attribute])) {
                    $this->_addMessages(
                        [
                            sprintf(
                                $this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL_OR_PATH),
                                $attribute
                            )
                        ]
                    );
                    $valid = false;
                }
            }
        }
        if (isset($value[self::ADDITIONAL_IMAGES]) && strlen($value[self::ADDITIONAL_IMAGES])) {
            foreach (explode(self::ADDITIONAL_IMAGES_DELIMITER, $value[self::ADDITIONAL_IMAGES]) as $image) {
                if (!$this->checkPath($image) && !$this->checkValidUrl($image)) {
                    $this->_addMessages(
                        [
                            sprintf(
                                $this->context->retrieveMessageTemplate(self::ERROR_INVALID_MEDIA_URL_OR_PATH),
                                self::ADDITIONAL_IMAGES
                            )
                        ]
                    );
                    $valid = false;
                }
                break;
            }
        }
        return $valid;
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function checkValidUrl($string)
    {
        return preg_match(self::URL_REGEXP, $string);
    }
}
