<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class Text
{
    private $name;

    private $text;

    private $length = 1;

    private $index = 0;

    /**
     * Constructor
     *
     * @param string $text
     */
    public function __construct($text)
    {
        $this->name = 'text';

        $this->text = $text;
    }

    /**
     * Gets the variable name
     *
     * @return string The name of the variable
     */
    public function getName()
    {
        return $this->name;
    }

    public function execute($context)
    {
        if ($this->index == 0) {
            $this->index = 1;

            return $this->text;
        }
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'index' => $this->index,
            'length' => $this->length
        ];
    }

    public function fromArray($array)
    {
        $this->index = $array['index'];
        $this->length = $array['length'];
    }

    public function reset()
    {
        $this->index = 0;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getLength()
    {
        return $this->length;
    }
}
