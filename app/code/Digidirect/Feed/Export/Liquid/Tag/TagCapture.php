<?php

namespace Digidirect\Feed\Export\Liquid\Tag;

class TagCapture extends \Digidirect\Feed\Export\Liquid\Block
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $to;

    /**
     * @param string $markup
     * @param array &$tokens
     * @throws \Exception
     */
    public function __construct($markup, &$tokens)
    {
        $syntaxRegexp = new \Digidirect\Feed\Export\Liquid\Regexp('/(\w+)/');
        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens);
        } else {
            throw new \Exception("Syntax Error in 'capture' - Valid syntax: assign [var] = [source]"); // harry
        }
    }

    /**
     * Renders the block
     *
     * @param Context &$context
     * @return string
     */
    public function execute($context)
    {
        $context->push();
        $result = $this->executeAll($this->nodeList, $context);
        $context->pop();

        $context->set($this->to, $result);

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->resetNodes();
    }

    /**
     * {@inheritdoc}
     */
    protected function resetNodes()
    {
        foreach ($this->nodeList as $token) {
            $token->reset();
        }
    }
}
