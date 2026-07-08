<?php

namespace Digidirect\Blog\Block\Widget;

class Search extends AbstractWidget
{
    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->getRequest()->getParam('s', '');
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->urlModel->getBlogListUrl();
    }
}
