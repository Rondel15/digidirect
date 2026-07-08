<?php
namespace Digidirect\AbstractEntity\Ui\Component\Listing\Column;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\RequestInterface;
use Digidirect\AbstractEntity\Model\Registry\Constants;

/**
 * Class RelatedEntityAction
 * @package Digidirect\AbstractEntity\Ui\Component\Listing\Column
 */
class RelatedEntityAction extends Column
{
    const URL_PATH_UNASSIGN = 'digidirect_abstractentity/abstractentity/unassign';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $_request;

    /**
     * RelatedEntityAction constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_request = $request;
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

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['unassing'] = [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_UNASSIGN,
                        [
                            'id' => $item['entity_id'],
                            AbstractEntityInterface::ATTRIBUTE_SET_ID =>
                            $this->_request->getParam('current_attribute_set_id', 0),
                            'parent_id' => $this->_request->getParam('current_entity_id', 0)
                        ]
                    ),
                    'label' => __('Unassign'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
