<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Test\Unit\ViewModel\Popup;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Filter\Template;
use Magento\Framework\View\LayoutFactory;
use PHPUnit\Framework\TestCase;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type;
use Plumrocket\Newsletterpopup\Model\Popup;
use Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer;

/**
 * @since 4.6.0
 */
class RendererTest extends TestCase
{

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Plumrocket\Newsletterpopup\Model\Popup
     */
    private $popupMock;

    protected function setUp(): void
    {
        $filterTemplateMock = $this->createMock(Template::class);
        $filterTemplateMock->method('filter')->willReturnArgument(0);
        $filterProviderMock = $this->createMock(FilterProvider::class);
        $filterProviderMock->method('getPageFilter')->willReturn($filterTemplateMock);

        $this->model = new Renderer(
            $this->createMock(LayoutFactory::class),
            $filterProviderMock,
            $this->createMock(JsConfig::class)
        );
        $this->popupMock = $this->createMock(Popup::class);
    }

    /**
     * @covers \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer::buildCss
     */
    public function testAddingPopupId(): void
    {
        $this->popupMock->method('getId')->willReturn('42');
        $this->popupMock->method('getType')->willReturn(Type::MODAL);
        $this->popupMock->method('getCss')->willReturn('.newspopup_up_bg span {font-size:5px;}');
        self::assertSame(
            '#newspopup_up_bg_42 span {font-size:5px;}',
            $this->model->buildCss($this->popupMock)
        );
    }
}
