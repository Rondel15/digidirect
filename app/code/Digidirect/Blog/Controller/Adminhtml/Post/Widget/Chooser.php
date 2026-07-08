<?php

namespace Digidirect\Blog\Controller\Adminhtml\Post\Widget;

use Magento\Backend\App\Action;

class Chooser extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Digidirect_Blog::blog';

    /**
     * Chooser Source action
     *
     * @return void
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');

        $postsGrid = $this->_view->getLayout()->createBlock(
            \Digidirect\Blog\Block\Adminhtml\Post\Widget\Chooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );
        $html = $postsGrid->toHtml();

        $this->getResponse()->setBody($html);
    }
}
