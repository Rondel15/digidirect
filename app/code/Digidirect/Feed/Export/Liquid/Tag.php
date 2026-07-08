<?php

namespace Digidirect\Feed\Export\Liquid;
/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
abstract class Tag
{
    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $markup;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array  $tokens
     * @return \Digidirect\Feed\Export\Liquid\Tag
     */
    public function __construct($markup, &$tokens)
    {
        $this->markup = $markup;

        $this->parse($tokens);
    }

    /**
     * Parse the given tokens
     *
     * @param array $tokens
     */
    public function parse(&$tokens)
    {
    }

    /**
     * Extracts tag attributes from a markup string
     *
     * @param string $markup
     */
    public function extractAttributes($markup)
    {
        $this->attributes = [];

        $attribute_regexp = new Regexp(Template::LIQUID_TAG_ATTRIBUTES);

        $matches = $attribute_regexp->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Returns the name of the tag
     *
     * @return string
     */
    public function name()
    {
        return strtolower(get_class($this));
    }

    /**
     * Render the tag with the given context
     *
     * @param Context $context
     * @return string
     */
    public function execute($context)
    {
        return '';
    }
}
