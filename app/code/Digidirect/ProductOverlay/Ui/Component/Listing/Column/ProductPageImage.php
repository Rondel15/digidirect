<?php

namespace Digidirect\ProductOverlay\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ProductPageImage
 * @package Digidirect\ProductOverlay\Ui\Component\Listing\Column
 */
class ProductPageImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    const ALT_FIELD = 'name';

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var string
     */
    protected $_name = 'prod_img';

    /**
     * ProductPageImage constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Digidirect\ProductOverlay\Helper\Data $imageHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Digidirect\ProductOverlay\Helper\Data $imageHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlBuilder  = $urlBuilder;
        $this->_imageHelper = $imageHelper;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
                $overlay = new \Magento\Framework\DataObject($item);
                $img     = $overlay->getData($this->_name);
                if ($img) {
                    $item[$fieldName . '_src']      = $this->_imageHelper->getImageUrl($img);
                    $item[$fieldName . '_alt']      = $overlay->getData('name');
                    $item[$fieldName . '_link']     = $this->_urlBuilder->getUrl(
                        'digidirect_productoverlay/overlays/edit',
                        ['id' => $overlay->getOverlayId()]
                    );
                    $item[$fieldName . '_orig_src'] = $this->_imageHelper->getImageUrl($img);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;

        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
