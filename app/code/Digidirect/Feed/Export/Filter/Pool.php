<?php

namespace Digidirect\Feed\Export\Filter;

class Pool
{
    /**
     * @var object[]
     */
    protected $scopes;

    /**
     * Constructor
     *
     * @param array $scopes
     */
    public function __construct(
        array $scopes
    ) {
        $this->scopes = $scopes;
    }

    /**
     * List of scopes
     *
     * @return object[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Return full list of possible filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = [];
        foreach ($this->scopes as $scope) {
            $class = new \ReflectionClass($scope);

            /** @var \ReflectionMethod $method */
            foreach ($class->getMethods() as $method) {
                try {
                    $doc = $method->getDocComment();
                } catch (\Exception $e) {
                    continue;
                }

                if (!$doc) {
                    continue;
                }

                $shortDescription = $this->getDocCommentShortDescription($doc);
                if (!$shortDescription) {
                    continue;
                }

                $filter = [
                    'label' => __($shortDescription)->__toString(),
                    'value' => $method->getName(),
                    'args' => [],
                ];

                /** @var \ReflectionParameter $param */
                foreach ($method->getParameters() as $param) {
                    if ($param->getName() == 'input') {
                        continue;
                    }

                    $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : '';

                    $filter['args'][] = [
                        'value' => $param->getName(),
                        'label' => ucfirst($param->getName()),
                        'default' => $default
                    ];
                }

                $filters[] = $filter;
            }
        }

        return $filters;
    }

    /**
     * Parse the docblock
     *
     * @param string $docComment
     * @return string
     */
    protected function getDocCommentShortDescription($docComment)
    {
        // First remove doc block line starters
        $docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
        $docComment = ltrim($docComment, "\r\n"); // @todo should be changed to remove first and last empty line

        // Next parse out the tags and descriptions
        $shortDescription = '';
        $parsedDocComment = $docComment;
        $lineNumber = $firstBlandLineEncountered = 0;
        while (($newlinePos = strpos($parsedDocComment, "\n")) !== false) {
            $lineNumber++;
            $line = substr($parsedDocComment, 0, $newlinePos);

            $matches = [];

            if ((strpos($line, '@') === 0)
                && (preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment, $matches))
            ) {
                $parsedDocComment = str_replace($matches[1] . $matches[2], '', $parsedDocComment);
            } else {
                if ($lineNumber < 3 && !$firstBlandLineEncountered) {
                    $shortDescription .= $line . "\n";
                }

                if ($line == '') {
                    $firstBlandLineEncountered = true;
                }

                $parsedDocComment = substr($parsedDocComment, $newlinePos + 1);
            }
        }

        return $shortDescription;
    }
}
