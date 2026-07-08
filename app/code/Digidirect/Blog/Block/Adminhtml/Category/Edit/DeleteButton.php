<?php

namespace Digidirect\Blog\Block\Adminhtml\Category\Edit;

use Digidirect\Blog\Model\CurrentStoreFetcher;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getCategoryId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            [
                'id' => $this->getCategoryId(),
                CurrentStoreFetcher::PARAM_STORE => $this->request->getParam(CurrentStoreFetcher::PARAM_STORE),
            ]
        );
    }
}
