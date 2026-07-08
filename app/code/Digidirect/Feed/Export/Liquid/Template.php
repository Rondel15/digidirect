<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */

class Template
{
    /**
     * Separator between filters
     *
     */
    const LIQUID_FILTER_SEPARATOR = '\|';

    /**
     * Separator for arguments
     *
     */
    const LIQUID_ARGUMENT_SEPARATOR = ',';

    /**
     * Separator for argument names and values
     *
     */
    const LIQUID_FILTER_ARGUMENT_SEPARATOR = ':';

    /**
     * Separator for variable attributes
     *
     */
    const LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR = '.';

    /**
     * Tag start
     *
     */
    const LIQUID_TAG_START = '{%';

    /**
     * Tag end
     *
     */
    const LIQUID_TAG_END = '%}';

    /**
     * Variable start
     *
     */
    const LIQUID_VARIABLE_START = '{{';

    /**
     * Variable end
     *
     */
    const LIQUID_VARIABLE_END = '}}';

    /**
     * The characters allowed in a variable
     *
     */
    const LIQUID_ALLOWED_VARIABLE_CHARS = '[a-zA-Z_.-:]';

    /**
     * Regex for quoted fragments
     *
     */
    const LIQUID_QUOTED_FRAGMENT = '"[^"]+"|\'[^\']+\'|[^\s,|]+';

    /**
     * Regex for recognizing tab attributes
     *
     */
    const LIQUID_TAG_ATTRIBUTES = '/(\w+)\s*\:\s*(' . Template::LIQUID_QUOTED_FRAGMENT . ')/';

    /**
     * Regex used to split tokens
     *
     */
    const LIQUID_TOKENIZATION_REGEXP = '/('
    . Template::LIQUID_TAG_START . '.*?' . Template::LIQUID_TAG_END
    . '|'
    . Template::LIQUID_VARIABLE_START . '.*?' . Template::LIQUID_VARIABLE_END
    . ')/';

    /**
     * @var Document The _root of the node tree
     */
    private $root;

    /**
     * @var array Globally included filters
     */
    private $filters;

    /**
     * @var array Custom tags
     */
    private static $tags = [];

    /**
     * Constructor
     *
     * @return \Digidirect\Feed\Export\Liquid\Template
     */
    public function __construct()
    {
        $this->filters = [];
    }

    /**
     *
     *
     * @return \Digidirect\Feed\Export\Liquid\Document
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Register custom Tags
     *
     * @param string $name
     * @param string $class
     */
    public function registerTag($name, $class)
    {
        self::$tags[$name] = $class;
    }

    /**
     *
     *
     * @return array
     */
    public static function getTags()
    {
        return self::$tags;
    }

    /**
     * Register the filter
     *
     * @param array $filter
     * @return $this
     */
    public function registerFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Register filters
     *
     * @param array[] $filters
     * @return $this
     */
    public function registerFilters($filters)
    {
        foreach ($filters as $filter) {
            $this->filters[] = $filter;
        }

        return $this;
    }

    /**
     * Tokenizes the given source string
     *
     * @param string $source
     * @return array
     */
    public static function tokenize($source)
    {
        return (!$source)
            ? []
            : preg_split(
                Template::LIQUID_TOKENIZATION_REGEXP,
                $source,
                null,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
    }

    /**
     * Parses the given source string
     *
     * @param string $source
     *
     * @return $this
     */
    public function parse($source)
    {
        $tokens = Template::tokenize($source);

        foreach ($tokens as $idx => $token) {
            $tagRegexp = new Regexp(
                '/^'
                . Template::LIQUID_TAG_START
                . '\s*(\w+)\s*(.*)?'
                . Template::LIQUID_TAG_END
                . '$/'
            );

            if ($tagRegexp->match($token)) {
                // if is tag (if, for), need remove PHP_EOL before
                if ($idx > 0) {
                    $t = explode("\n", $tokens[$idx - 1]);
                    $end = end($t);
                    if (strlen(trim($end)) == 0) {
                        array_pop($t);
                    }
                    $tokens[$idx - 1] = implode("\n", $t);
                }

            }
        }

        $this->root = new Document($tokens);

        return $this;
    }

    /**
     * Renders the current template
     *
     * @param Context $context
     * @return string
     */
    public function execute($context)
    {
        $context->setTemplate($this);

        return $this->root->execute($context);
    }

    public function getIndex()
    {
        return $this->root->getIndex();
    }

    public function getLength()
    {
        return $this->root->getLength();
    }

    public function toArray()
    {
        return $this->root->toArray();
    }

    public function fromArray($array)
    {
        $this->root->fromArray($array);

        return $this;
    }
}
