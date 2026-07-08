<?php
namespace Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Option\ArrayInterface;

class VisibleOnFrontend extends AbstractSource implements SourceInterface, OptionSourceInterface, ArrayInterface
{
    const VISIBLE_ON_FRONTEND_ENABLED = 1;

    const VISIBLE_ON_FRONTEND_DISABLED = 0;

    /**#@-*/

    /**
     * Retrieve Visible Status Ids
     *
     * @return int[]
     */
    public function getVisibleStatusIds()
    {
        return [self::VISIBLE_ON_FRONTEND_ENABLED];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [self::VISIBLE_ON_FRONTEND_ENABLED => __('Enabled'), self::VISIBLE_ON_FRONTEND_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
