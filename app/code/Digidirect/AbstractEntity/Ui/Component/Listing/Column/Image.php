<?php
namespace Digidirect\AbstractEntity\Ui\Component\Listing\Column;

use Digidirect\AbstractEntity\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\DataObject;
use Magento\Ui\Component\Listing\Columns\Column;

class Image extends Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Digidirect\AbstractEntity\Helper\Image
     */
    protected $imageHelper;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Digidirect\AbstractEntity\Helper\Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageHelper $imageHelper,
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
                $row = new DataObject($item);
                $imageHelper = $this->imageHelper->init($row, $fieldName);
                $item[$fieldName . '_src'] = $imageHelper->resize(50, 50)->getUrl();
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_orig_src'] = $imageHelper->getOriginalImageUrl();
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'digidirect_abstractentity/abstractentity/edit',
                    ['id' => $row->getEntityId()]
                );
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
