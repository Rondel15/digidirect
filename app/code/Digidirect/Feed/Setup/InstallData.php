<?php

namespace Digidirect\Feed\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Digidirect\Feed\Model\Config\Source\Rule as RuleSource;
use Digidirect\Feed\Model\Config\Source\Template as TemplateSource;
use Digidirect\Feed\Model\RuleFactory;
use Digidirect\Feed\Model\TemplateFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var TemplateSource
     */
    protected $templateSource;

    /**
     * @var RuleSource
     */
    protected $ruleSource;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * {@inheritdoc}
     *
     * @param TemplateSource $templateSource
     * @param RuleSource $ruleSource
     * @param TemplateFactory $templateFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        TemplateSource $templateSource,
        RuleSource $ruleSource,
        TemplateFactory $templateFactory,
        RuleFactory $ruleFactory
    ) {
        $this->templateSource = $templateSource;
        $this->ruleSource = $ruleSource;
        $this->templateFactory = $templateFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $templatesPath = dirname(__FILE__) . '/data/template/';
        foreach (scandir($templatesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->templateFactory->create()->import($templatesPath . $file);
        }

        $rulesPath = dirname(__FILE__) . '/data/rule/';
        foreach (scandir($rulesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->ruleFactory->create()->import($rulesPath . $file);
        }
    }
}
