<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\AI\Ui\Component\IntegrationsListing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Digidirect\AI\Model;

/**
 * Class PageActions
 */
class Status extends Column
{
    /** Url path */
    const AI_URL_PATH_RUN = 'digidirect_ai/integration/run';

    /**
     * UrlBuilder
     *
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * UrlInterface
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        return $dataSource;

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item['status']) || !$item['status']) {
                    $item['status'] = '<span class="grid-severity-notice">' . __('No Information') . '</span>';
                } elseif ($item['status'] == Model\Integrations::STATUS_PENDING) {
                    $item['status'] = '<span class="grid-severity-notice">' . $item['status'] . '</span>';
                } elseif ($item['status'] == Model\Integrations::STATUS_PROCESSING) {
                    $item['status'] = '<span class="grid-severity-minor"><span>' . $item['status'] . '</span></span>';
                } elseif ($item['status'] == Model\Integrations::STATUS_ERROR) {
                    $item['status'] = '<span class="grid-severity-critical"><span>'
                        . $item['status']
                        . '</span></span>';
                    $item['status_css_classes'] = 'grid-severity-critical';
                }
            }
        }
    }
}
