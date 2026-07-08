<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Renderer;

use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;

class Time extends AbstractElement
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param AdminhtmlHelper $adminhtmlHelper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        DataHelper $dataHelper,
        $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->_escaper = $escaper;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setType('text');
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $htmlStart = '<div class="admin__field field field-extended_time  with-note">'
            . '<label class="label admin__field-label">'
            . '<span>' . $this->getLabel() . '</span>'
            . '</label><div class="admin__field-control control">';

        $htmlEnd = '</div></div>' . $this->getAfterElementHtml();

        /* Get current extended time */
        $time = $this->dataHelper->extendedTimeToArray(
            $this->getValue(),
            $this->getName()
        );

        /* Get the first of formats by field param */
        $formats = $this->dataHelper->getExtTimeFormats($this->getName());
        $fields = array_shift($formats);
        $formatLabels = [];

        if (!is_array($fields)) {
            return $htmlStart . __('Unexpected field format.') . $htmlEnd;
        }

        /* Initialize empty html for new field */
        $htmlFields = '';

        foreach ($this->dataHelper->getAllowedExtTimeFields() as $field) {
            if (is_array($fields) && in_array($field, $fields)) {
                $elId = implode('_', [$this->getHtmlId(), $field]);
                $htmlFields .= '<select id="' . $elId
                    . '" name="' . $this->getName() . '[' . $field . ']" '
                    . $this->serialize($this->getHtmlAttributes())
                    . ' style="width:40px">' . PHP_EOL;

                $range = [];
                $delimiter = '';

                switch ($field) {
                    case DataHelper::FORMAT_DAY:
                        $range = range(0, 89);
                        $formatLabels[] = __('Days');
                        break;
                    case DataHelper::FORMAT_HOUR:
                        $range = range(0, 23);
                        $delimiter = ':';
                        $formatLabels[] = __('Hours');
                        break;
                    case DataHelper::FORMAT_MIN:
                        $range = range(0, 59);
                        $delimiter = in_array(DataHelper::FORMAT_SEC, $fields)
                            ? ':' : '';
                        $formatLabels[] = __('Minutes');
                        break;
                    case DataHelper::FORMAT_SEC:
                        $range = range(0, 59);
                        $formatLabels[] = __('Seconds');
                        break;
                }

                foreach ($range as $item) {
                    $item = str_pad($item, 2, '0', STR_PAD_LEFT);
                    $htmlFields .= '<option value="' . $item . '" '
                        . ($time[$field] == $item ? 'selected="selected"' : '')
                        . '>' . $item . '</option>';
                }

                $htmlFields .= '</select>&nbsp;' . $delimiter . '&nbsp;' . PHP_EOL;
            }
        }

        /* Html comment for extended time field */
        $htmlComment = '<p class="note"><span>'
            . __('Time format') . ': '
            . '<u>' . implode('</u>,&nbsp;<u>', $formatLabels) . '</u>.<br/>'
            . '</span>' . $this->getNote() . '</p>';

        $html = $htmlStart . $htmlFields . $htmlComment . $htmlEnd;

        return $html;
    }
}
