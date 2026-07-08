<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\ViewModel\Popup;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutFactory;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Block\Widget\Form;
use Plumrocket\Newsletterpopup\Model\Config\Source\Method;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type;
use Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface;

/**
 * @since 4.6.0
 */
class Renderer implements ArgumentInterface
{

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig
     */
    private $jsConfig;

    /**
     * @param \Magento\Framework\View\LayoutFactory                $layoutFactory
     * @param \Magento\Cms\Model\Template\FilterProvider           $filterProvider
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig $jsConfig
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        FilterProvider $filterProvider,
        JsConfig $jsConfig
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->filterProvider = $filterProvider;
        $this->jsConfig = $jsConfig;
    }

    /**
     * Render popup html with inline css.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return string
     */
    public function render(PopupInterface $popup): string
    {
        $layout = $this->layoutFactory->create();
        $popupBlock = $layout->createBlock(\Plumrocket\Newsletterpopup\Block\Popup::class);
        return <<<HTML
<div class="newspopup_up_bg pr-newsletter-popup__wrp"
     id="newspopup_up_bg_{$popup->getId()}"
     style="display: none;">
    {$popupBlock->toHtml()}
</div>
HTML;
    }

    /**
     * Build CSS for specific popup.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface $template
     * @return string
     */
    public function buildCss(TemplateInterface $template): string
    {
        $style = $template->getCss();
        if ($template instanceof PopupInterface && $template->getType() === Type::WIDGET_TEMPLATE) {
            $style .= PHP_EOL . '.newspopup_up_bg {z-index: 0;}';
        }

        // Use secure url for fonts.
        $style = preg_replace(
            '#{{view url=(.)Plumrocket_Newsletterpopup::css/font/(.+?)}}#ui',
            '{{view url=$1Plumrocket_Newsletterpopup::css/font/$2$1 _secure=$1true$1}}',
            $style
        );

        $style = $this->filterProvider->getPageFilter()->filter($style);
        $id = '#newspopup_up_bg_' . $template->getId();

        $style = preg_replace(
            [
                '/,(\s*)(\.)/m', // do not add (\.|#)  NEVER!!!
                '/^(\s*)(\.|#)/m'
            ],
            [
                ', ' . $id . ' $2',
                '$1' . $id . ' $2'
            ],
            $style
        );

        return str_replace(
            [
                $id . ' .newspopup_up_bg',
                $id . ' .newspopup-blur',
                $id . ' .newspopup_ov_hidden',
                $id . ' .' . Form::CSS_CLASS_NAME,
            ],
            [
                $id,
                '.newspopup-blur-' . $template->getId(),
                '.newspopup_ov_hidden-' . $template->getId(),
                $id . '.' . Form::CSS_CLASS_NAME
            ],
            $style
        );
    }

    /**
     * Get popup JS config.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @param array                                               $additional
     * @return array
     */
    public function getJsSettings(PopupInterface $popup, array $additional = []): array
    {
        if ($popup->getType() === Type::WIDGET_TEMPLATE) {
            $additional = array_merge([
                'isWidget'      => true,
                'display_popup' => Method::MANUALLY,
                'delay_time'    => 0,
                'page_scroll'   => 0,
                'css_selector'  => '',
            ], $additional);
        }
        return $this->jsConfig->get($popup, $additional);
    }
}
