<?php

namespace Digidirect\AI\Ui\Component\IntegrationsListing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class IntegrationsActions extends Column
{
    /** Url path */
    const AI_URL_PATH_RUN = 'digidirect_ai/integration/run';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::AI_URL_PATH_RUN
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
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
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['process_code']) && !empty($item['can_admin_run'])) {
                    if (isset($item['status'])
                        && $item['status'] == \Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED
                    ) {
                        $item[$fieldName]['visible'] = false;
                        continue;
                    }

                    $btnLabel = __('Run');
                    if (isset($item['status'])
                        && empty($item['multiple_run'])
                        && $item['status'] == \Digidirect\AI\Model\Integrations\Integrations::STATUS_PROCESSING
                    ) {
                        $btnLabel = __('View State');
                    }
                    $item[$fieldName]['label'] = $btnLabel;
                }
            }
        }
        return $dataSource;
    }
}
