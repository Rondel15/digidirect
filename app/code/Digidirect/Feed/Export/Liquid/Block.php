<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class Block extends Tag
{
    protected $nodeList;

    protected $index = 0;
    protected $length = 0;

    /**
     * @return array
     */
    public function getNodeList()
    {
        return $this->nodeList;
    }

    /**
     * Parses the given tokens
     *
     * @param array &$tokens
     * @return void
     * @throws \Exception
     */
    public function parse(&$tokens)
    {
        $startRegexp = new Regexp('/^' . Template::LIQUID_TAG_START . '/');

        $tagRegexp = new Regexp('/^' . Template::LIQUID_TAG_START . '\s*(\w+)\s*(.*)?' . Template::LIQUID_TAG_END . '$/');
        $variableStartRegexp = new Regexp('/^' . Template::LIQUID_VARIABLE_START . '/');

        $this->nodeList = [];

        if (!is_array($tokens)) {
            return;
        }

        $tags = Template::getTags();

        while (count($tokens)) {
            $token = array_shift($tokens);

            if ($startRegexp->match($token)) {
                if ($tagRegexp->match($token)) {
                    // if we found the proper block delimiter just end parsing here and let the outer block proceed
                    if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
                        return;
                    }

                    if (array_key_exists($tagRegexp->matches[1], $tags)) {
                        $tagName = $tags[$tagRegexp->matches[1]];
                    } else {
                        // search for a defined class of the right name, instead of searching in an array
                        $tagName = '\Digidirect\Feed\Export\Liquid\Tag\Tag' . ucwords($tagRegexp->matches[1]);
                        $tagName = (class_exists($tagName) === true) ? $tagName : null;
                    }

                    if (class_exists($tagName)) {
                        $this->nodeList[] = new $tagName($tagRegexp->matches[2], $tokens);

                        if ($tagRegexp->matches[1] == 'extends') {
                            return;
                        }
                    } else {
                        $this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
                    }
                } else {
                    throw new \Exception("Tag $token was not properly terminated");
                }
            } elseif ($variableStartRegexp->match($token)) {
                $this->nodeList[] = $this->createVariable($token);
            } elseif ($token != '') {
                $this->nodeList[] = new Text($token);
            }
        }

        $this->assertMissingDelimitation();
    }

    /**
     * An action to execute when the end tag is reached
     *
     */
    protected function endTag()
    {
    }

    /**
     * Handler for unknown tags
     *
     * @param string $tag
     * @throws \Exception
     */
    protected function unknownTag($tag, $params, &$tokens)
    {
        switch ($tag) {
            case 'else':
                throw new \Exception($this->getBlockName() . " does not expect else tag");

            case 'end':
                throw new \Exception("'end' is not a valid delimiter for " . $this->getBlockName() . " tags.");

            default:
                throw new \Exception("Unknown tag $tag");
        }

    }

    /**
     * Returns the string that delimits the end of the block
     *
     * @return string
     */
    public function blockDelimiter()
    {
        return "end" . $this->getBlockName();
    }

    /**
     * Returns the name of the block
     *
     * @return string
     */
    private function getBlockName()
    {
        return str_replace('digidirect\feed\export\liquid\tag\tag', '', strtolower(get_class($this)));
    }

    /**
     * Create a variable for the given token
     *
     * @param string $token
     * @return Variable
     * @throws \Exception
     */
    private function createVariable($token)
    {
        $variableRegexp = new Regexp(
            '/^'
            . Template::LIQUID_VARIABLE_START
            . '(.*)'
            . Template::LIQUID_VARIABLE_END
            . '$/'
        );
        if ($variableRegexp->match($token)) {
            return new Variable($variableRegexp->matches[1]);
        }

        throw new \Exception("Variable $token was not properly terminated");
    }

    /**
     * Render the block.
     *
     * @param Context $context
     * @return string
     */
    public function execute($context)
    {
        return $this->executeAll($this->nodeList, $context);
    }

    public function getIndex()
    {
        $index = $this->index;

        foreach ($this->nodeList as $token) {
            $index += $token->getIndex();
        }

        return $index;
    }

    public function getLength()
    {
        $length = $this->length;

        foreach ($this->nodeList as $token) {
            $length += $token->getLength();
        }

        return $length;
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->nodeList as $token) {
            $array[] = $token->toArray();
        }

        return $array;
    }

    public function fromArray($array)
    {
        foreach ($this->nodeList as $index => $token) {
            if (isset($array[$index])) {
                $token->fromArray($array[$index]);
            }
        }
    }

    /**
     * This method is called at the end of parsing, and will through an error unless
     * this method is subclassed, like it is for LiquidDocument
     *
     * @return bool
     * @throws \Exception
     */
    public function assertMissingDelimitation()
    {
        throw new \Exception($this->getBlockName() . " tag was never closed");
    }

    /**
     * Renders all the given nodelist's nodes
     *
     * @param array $list
     * @param Context $context
     * @return string
     */
    protected function executeAll(array $list, &$context)
    {
        $result = '';
        foreach ($list as $token) {
            $token = (is_object($token) && method_exists($token, 'execute')) ? $token->execute($context) : $token;
            $token = is_array($token) ? print_r($token, true) : $token;
            $result .= $token;

            if ($context->isBreak) {
                break;
            }
        }

        $context->isTimeout();

        return $result;
    }
}
