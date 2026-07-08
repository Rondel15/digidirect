<?php
namespace Digidirect\AI\Ui\Component\QueueListing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Staging\Model\VersionManager;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;

/**
 * Class QueueActions
 *
 * @package Digidirect\AI\Ui\Component\LogsListing\Column
 */
class QueueActions extends Column
{
    /**
     * Url path delete
     */
    const URL_PATH_DELETE = 'digidirect_ai/queue/delete';

    /**
     * Url path run
     */
    const URL_PATH_RUN = 'digidirect_ai/queue/run';

    /**
     * Preview url
     */
    const URL_PATH_DETAILS = 'digidirect_ai/queue/details';

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * QueueActions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return []
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName] = [
                    'details' => [
                        'href' => $this->urlBuilder->getUrl(static::URL_PATH_DETAILS, ['queue_id' => $item['id']]),
                        'label' => __('View Details')
                    ]
                ];

                if (($item[QueueInterface::STATE] == QueueState::STATE_CLOSED
                    && $item[QueueInterface::STATUS] == QueueStatus::STATUS_FAILED)
                    || $item[QueueInterface::STATE] == QueueState::STATE_PENDING_DEPENDS
                ) {
                    $item[$fieldName]['run'] = [
                        'href' => $this->urlBuilder->getUrl(static::URL_PATH_RUN, ['queue_id' => $item['id']]),
                        'label' => __('Force Run')
                    ];
                }

                if ($item[QueueInterface::STATE] == QueueState::STATE_CLOSED
                    && $item[QueueInterface::STATUS] == QueueStatus::STATUS_FAILED) {
                    $item[$fieldName]['delete'] = [
                        'href'  => $this->urlBuilder->getUrl(static::URL_PATH_DELETE, ['queue_id' => $item['id']]),
                        'label' => __('Delete')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
