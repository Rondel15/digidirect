<?php

namespace Digidirect\AI\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Escaper;

class Text extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param Escaper $escaper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Escaper $escaper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $configuration = $this->getConfiguration();
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName] = trim($item[$fieldName]);

                if (!isset($configuration['escape']) || $configuration['escape']) {
                    $item[$fieldName] = $this->escaper->escapeHtml($item[$fieldName]);
                }

                if (!empty($configuration['nl2br'])) {
                    $item[$fieldName] = nl2br($item[$fieldName]);
                }
            }
        }
        return parent::prepareDataSource($dataSource);
    }
}
