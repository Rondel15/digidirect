<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Option;

/**
 * Class Layout
 * @package Digidirect\AbstractAttributes\Ui\Component\Option
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     */
    public function __construct(\Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('No layout updates')]);
        }
        return $this->_options;
    }
}
