<?php

namespace Digidirect\Utilities\Preference\Magento\Framework\View\Result;

use Magento\Framework;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View;

class Page extends \Magento\Framework\View\Result\Page
{
    /**
     * Utilities Animation helper
     *
     * @var \Digidirect\Utilities\Helper\Animation
     */
    protected $_utilitiesHelper;

    /**
     * @var Framework\Escaper
     */
    protected $_escaper;

    /**
     * Page constructor.
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param View\Layout\ReaderPool $layoutReaderPool
     * @param Framework\Translate\InlineInterface $translateInline
     * @param View\Layout\BuilderFactory $layoutBuilderFactory
     * @param View\Layout\GeneratorPool $generatorPool
     * @param View\Page\Config\RendererFactory $pageConfigRendererFactory
     * @param View\Page\Layout\Reader $pageLayoutReader
     * @param \Digidirect\Utilities\Helper\Animation $animation
     * @param string $template
     * @param bool $isIsolated
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        \Digidirect\Utilities\Helper\Animation $animation,
        $template,
        $isIsolated = false
    ) {
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $pageConfigRendererFactory,
            $pageLayoutReader,
            $template,
            $isIsolated
        );
        $this->_escaper = $context->getEscaper();
        $this->_utilitiesHelper = $animation;
    }

    /**
     * Overwritten core function - add variables to template before rendering page
     *
     * @return string
     */
    protected function renderPage()
    {
        $this->assign(
            [
                'loaderIcon' => $this->_getLoader(),
                'loaderText' => $this->_utilitiesHelper->isEnabled() ? $this->_escaper->escapeQuote(__($this->_utilitiesHelper->getText())) : null,
            ]
        );
        return parent::renderPage();
    }

    /**
     * Get loader
     *
     * @return string
     */
    public function _getLoader()
    {
        if ($this->_utilitiesHelper->isEnabled() && $this->_utilitiesHelper->getImage()) {
            return $this->_utilitiesHelper->getImage();
        }
        return $this->getViewFileUrl('images/loader-2.gif');
    }
}
