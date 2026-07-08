<?php

namespace Digidirect\Feed\Export\Filter;

class StringFilter
{
    /**
     * Format csv column value
     *
     * @param string $input
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function csv($input, $delimiter = ',', $enclosure = '')
    {
        if (is_scalar($input)) {
            $escapeChar = '\\';
            if (($delimiter && strpos($input, $delimiter) !== false) ||
                ($enclosure && strpos($input, $enclosure) !== false) ||
                strpos($input, "\n") !== false ||
                strpos($input, "\r") !== false ||
                strpos($input, "\t") !== false ||
                strpos($input, ' ') !== false
            ) {
                $str = $enclosure;
                $escaped = 0;
                $len = strlen($input);
                for ($i = 0; $i < $len; $i++) {
                    if ($input[$i] == $escapeChar) {
                        $escaped = 1;
                    } elseif (!$escaped && $input[$i] == $enclosure) {
                        $str .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $str .= $input[$i];
                }
                $str .= $enclosure;

                $input = $str;
            }
        }

        return $input;
    }

    /**
     * Replace
     *
     * Replaces all occurrences of a string with a substring.
     *
     * @param string $input
     * @param string $search
     * @param string $replace
     * @return string
     */
    public function replace($input, $search = '', $replace = '')
    {
        return is_string($input) ? str_replace($search, $replace, $input) : $input;
    }

    /**
     * Lowercase
     *
     * Converts a string into lowercase.
     *
     * @param string $input
     * @return string
     */
    public function lowercase($input)
    {
        return is_string($input) ? strtolower($input) : $input;
    }

    /**
     * Uppercase
     *
     * Converts a string into uppercase.
     *
     * @param string $input
     * @return string
     */
    public function uppercase($input)
    {
        return is_string($input) ? strtoupper($input) : $input;
    }

    /**
     * Append
     *
     * Appends characters to a string.
     *
     * @param string $input
     * @param string $suffix
     * @return string
     */
    public function append($input, $suffix = '')
    {
        return is_string($input) ? $input . $suffix : $input;
    }

    /**
     * Prepend
     *
     * Prepends characters to a string.
     *
     * @param string $input
     * @param string $prefix
     * @return string
     */
    public function prepend($input, $prefix = '')
    {
        return is_string($input) ? $prefix . $input : $input;
    }

    /**
     * Capitalize
     *
     * Capitalizes the first word in a string.
     *
     * @param string $input
     * @return string
     */
    public function capitalize($input)
    {
        return is_string($input) ? ucfirst($input) : $input;
    }

    /**
     * Escape
     *
     * Escapes a string.
     *
     * @param string $input
     * @return string
     */
    public function escape($input)
    {
        return is_string($input) ? htmlspecialchars($input) : $input;
    }

    /**
     * Newline to <br>
     *
     * Inserts a <br > linebreak HTML tag in front of each line break in a string.
     *
     * @param string $input
     * @return string
     */
    public function nl2br($input)
    {
        return is_string($input) ? nl2br($input) : $input;
    }

    /**
     * Remove
     *
     * Removes all occurrences of a substring from a string.
     *
     * @param string $input
     * @param string $text
     * @return string
     */
    public function remove($input, $text = '')
    {
        return is_string($input) ? str_replace($text, '', $input) : $input;
    }

    /**
     * Strip HTML tags
     *
     * Strips all HTML tags from a string.
     *
     * @param string $input
     * @return string
     */
    public function stripHtml($input)
    {
        return is_string($input) ? strip_tags($input) : $input;
    }

    /**
     * Truncate
     *
     * Truncates a string down to 'x' characters.
     *
     * @param string $input
     * @param int $len
     * @return string
     */
    public function truncate($input, $len = 0)
    {
        return is_string($input) ? substr($input, 0, intval($len)) : $input;
    }

    /**
     * Plain format
     *
     * Converts any text to plain
     *
     * @param string $input
     * @return string
     */
    public function plain($input)
    {
        // 194 -> 32
        $input = str_replace(' ', ' ', $input);

        $input = strip_tags($input);

        $input = str_replace('\\\'', '\'', $input);
        $input = preg_replace('/\s+/', ' ', $input);

        //{{block type="cms/block" block_id="product-3-in-1" template="cms/content.phtml"}}
        $input = preg_replace('/({{.*}})/is', '', $input);

        $input = trim($input);

        return $input;
    }

    /**
     * If Empty
     *
     * @param string $input
     * @param string $default
     * @return string
     */
    public function ifEmpty($input, $default = '')
    {
        if (!$input || $input == '') {
            return $default;
        }

        return $input;
    }

    /**
     * Format date
     *
     * Converts a string to specified date-time format.
     *
     * @param string $input
     * @param string $format
     * @return string
     */
    public function dateFormat($input, $format = 'd.m.Y')
    {
        return date($format, strtotime($input));
    }

    /**
     * Rtrim
     *
     * Strip whitespace (or other characters) from the end of a string.
     *
     * @param string $input
     * @param string $mask
     * @return string
     */
    public function rtrim($input, $mask = ' ')
    {
        return rtrim($input, $mask);
    }
}
