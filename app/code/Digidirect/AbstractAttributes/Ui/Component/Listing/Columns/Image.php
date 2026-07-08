<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Image
 * @package Digidirect\AbstractAttributes\Ui\Component\Listing\Columns
 */
class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Image
     */
    protected $imageHelper;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Digidirect\AbstractAttributes\Helper\Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Digidirect\AbstractAttributes\Helper\Image $imageHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $row = new \Magento\Framework\DataObject($item);
                $imageHelper = $this->imageHelper->init($row, $fieldName);
                $item[$fieldName . '_src'] = $imageHelper->resize(50, 50)->getUrl();
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'eaa/option/edit',
                    ['option_id' => $row->getOptionId()]
                );
                $item[$fieldName . '_orig_src'] = $imageHelper->getOriginalImageUrl();
            }
        }

        return $dataSource;
    }

    /**
     * Get image alt parameter
     * @param array $row
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
