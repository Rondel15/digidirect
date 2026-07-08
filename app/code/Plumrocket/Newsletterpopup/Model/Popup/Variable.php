<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Plumrocket\Newsletterpopup\Model\Popup\Variable\Product;

/**
 * @since 4.0.0
 */
class Variable
{
    /**
     * @var array
     */
    private $variables = [
        '{{popup_id}}' => 'Popup Identifier',
        '{{text_cancel}}' => 'Value for Text Labels Popup Settings -> Cancel Button',
        '{{text_title}}' => 'Value for Text Labels Popup Settings -> Title',
        '{{text_description}}' => 'Value for Text Labels Popup Settings -> Description',
        '{{form_fields}}' => 'Fields selected in General Settings -> Signup Form -> Enable Form Fields',
        '{{contact_lists}}' => 'Number of Integration Lists included in Integrations Tab',
        '{{text_submit}}' => 'Value for Text Labels Popup Settings -> Submit Button',
    ];

    /**
     * @var array
     */
    private $additionalVariables = [
        '{{media url="wysiwyg/image.png"}}' => '',
        '{{view url="Plumrocket_Newsletterpopup::images/image.png"}}' => '',
        '{{store direct_url="privacy-policy-cookie-restriction-mode"}}' => '',
    ];

    /**
     * Variable constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Product $productVariables
     * @param array                                                    $variables
     */
    public function __construct(Product $productVariables, array $variables = [])
    {
        $this->variables = array_merge($this->variables, $productVariables->getList());
        $this->additionalVariables = array_merge($this->additionalVariables, $variables);
    }

    /**
     * @param bool $withAdditional
     * @return array
     */
    public function getVariablesCode(bool $withAdditional = false): array
    {
        return array_keys($this->getVariablesArray($withAdditional));
    }

    /**
     * @param bool $withAdditional
     * @return array
     */
    public function getVariablesOptionArray(bool $withAdditional = false): array
    {
        $variables = $this->getVariablesArray($withAdditional);

        $options = [];
        foreach ($variables as $code => $label) {
            $options[] = [
                'label' => __($label),
                'value' => $code,
            ];
        }

        return $options;
    }

    /**
     * @param bool $withAdditional
     * @return array
     */
    protected function getVariablesArray(bool $withAdditional = false): array
    {
        $variables = $this->variables;

        if ($withAdditional) {
            $variables = array_merge($variables, $this->additionalVariables);
        }

        return $variables;
    }
}
