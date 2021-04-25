<?php

namespace modHelpers;

class Str
{
    /** @var string */
    protected $original;
    /** @var string */
    protected $string;

    public function __construct($string)
    {
        $this->original = $this->string = $string;
    }

    /**
     * Get the original string.
     * @return string
     */
    public function original()
    {
        return $this->original;
    }

    /**
     * Get the string.
     * @return string
     */
    public function get()
    {
        return $this->string;
    }

    /**
     * Cancel all manipulation with the string.
     * @return $this
     */
    public function undo()
    {
        $this->string = $this->original;

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.mb-substr.php
     * @return $this
     */
    public function substr()
    {
        $arguments = ['string' => $this->string] + func_get_args();
        $this->string = mb_substr(...array_filter($arguments));

        return $this;
    }

    /**
     * Replace one entry.
     * @param string $search
     * @param string $replace
     * @param bool $caseIgnoring
     * @return $this
     */
    public function replace($search, $replace = '', $caseIgnoring = false)
    {
        if (!empty($search)) {
            $func = $caseIgnoring ? 'mb_stripos' : 'mb_strpos';
            if (($position = $func($this->string, $search)) !== false) {
                $this->string = str_concat(mb_substr($this->string, 0, $position), $replace, mb_substr($this->string, $position + mb_strlen($search)));
            }
        }
        return $this;
    }

    /**
     * @param string $pattern
     * @param string $replace
     * @param bool $caseIgnoring
     * @return $this
     */
    public function replaceAll($pattern, $replace = '', $caseIgnoring = false)
    {
        if (!empty($pattern)) {
            $func = $caseIgnoring ? 'mb_eregi_replace' : 'mb_ereg_replace';
            $this->string = $func($pattern, $replace, $this->string);
        }
        return $this;
    }

    /**
     * Insert a string at the specified position.
     * @param $string
     * @param $position
     * @return $this
     */
    public function insert($string, $position)
    {
        if (!empty($string)) {
            $position = (int)$position > mb_strlen($this->string) ? mb_strlen($this->string) : $position - 1;
            $this->string = str_concat(mb_substr($this->string, 0, $position), $string, mb_substr($this->string, $position));
        }
        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.mb-strlen.php
     * @return int
     */
    public function length()
    {
        return mb_strlen($this->string);
    }

    /**
     * @see http://php.net/manual/en/function.mb-strtoupper.php
     * @return $this
     */
    public function toUpper()
    {
        $this->string = mb_strtoupper($this->string);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.mb-strtolower.php
     * @return $this
     */
    public function toLower()
    {
        $this->string = mb_strtolower($this->string);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.mb-convert-case.php
     * @param string $encoding
     * @return $this
     */
    public function ucWords($encoding = 'UTF-8')
    {
        $this->string = mb_convert_case($this->string, MB_CASE_TITLE, $encoding);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.ucfirst.php
     * @return $this
     */
    public function ucFirst()
    {
        $this->string = mb_strtoupper(mb_substr($this->string, 0, 1)) . mb_substr($this->string, 1);

        return $this;
    }

    /**
     * Erase the specifies range of the string.
     * @param $start
     * @param int $length
     * @return $this
     */
    public function erase($start, $length = 1)
    {
        if (mb_strlen($this->string) >= $start + 1) {
            $length = $length ?: 1;
            $this->string = str_concat(mb_substr($this->string, 0, $start), mb_substr($this->string, $start + $length));
        }
        return $this;
    }

    /**
     * Wrap the string with the prefix and suffix.
     * @param string $prefix
     * @param string $suffix
     * @return $this
     */
    public function wrap($prefix = '', $suffix = '')
    {
        $this->string = $prefix . $this->string . $suffix;

        return $this;
    }

    /**
     * Get the specified first symbols.
     * @param $length
     * @return $this
     */
    public function first($length)
    {
        $length = (int)$length;
        $this->string = mb_substr($this->string, 0, $length);

        return $this;
    }

    /**
     * Get the specified last symbols.
     * @param $length
     * @return $this
     */
    public function last($length)
    {
        $length = (int)$length;
        $this->string = mb_substr($this->string, $length * -1);

        return $this;
    }

    /**
     * Return true if the string is empty.
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->string);
    }

    /**
     * @see http://php.net/manual/en/function.explode.php
     * @param $delimiter
     * @param int|null $limit
     * @return array
     */
    public function explode($delimiter, $limit = null)
    {
        return explode($delimiter, $this->string, $limit);
    }

    /**
     * Limit the number of characters in the string.
     *
     * @param int $limit
     * @param string $ending
     * @return $this
     */
    public function limit($limit = 100, $ending = '...')
    {
        $this->string = str_limit($this->string, $limit, $ending);

        return $this;
    }

    /**
     * Get a substring between two tags.
     *
     * @param $start
     * @param $end
     * @param bool $greedy
     * @return $this
     */
    public function between($start, $end, $greedy = true)
    {
        $this->string = str_between($this->string, $start, $end, $greedy);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.htmlentities.php
     * @return $this
     */
    public function encode()
    {
        $arguments = ['string' => $this->string] + func_get_args();
        $this->string = call_user_func_array('htmlentities', $arguments);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.html-entity-decode.php
     * @return $this
     */
    public function decode()
    {
        $arguments = ['string' => $this->string] + func_get_args();
        $this->string = call_user_func_array('html_entity_decode', $arguments);

        return $this;
    }

    /**
     * Determine if the string matches a given pattern.
     * @see https://github.com/sergant210/modHelpers/blob/master/core/components/modhelpers/docs/en/str_match.md
     *
     * @param string $pattern
     * @param bool $case
     * @return bool
     */
    public function match($pattern, $case = false)
    {
        return str_match($this->string, $pattern, $case);
    }

    /**
     * @see http://php.net/manual/en/function.trim.php
     * @param string $character_mask
     * @return $this
     */
    public function trim($character_mask = " \t\n\r\0\x0B")
    {
        $this->string = trim($this->string, $character_mask);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.ltrim.php
     * @param string $character_mask
     * @return $this
     */
    public function ltrim($character_mask = " \t\n\r\0\x0B")
    {
        $this->string = ltrim($this->string, $character_mask);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.rtrim.php
     * @param string $character_mask
     * @return $this
     */
    public function rtrim($character_mask = " \t\n\r\0\x0B")
    {
        $this->string = rtrim($this->string, $character_mask);

        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function sha1($length = 0)
    {
        $this->string = sha1($this->string);
        if (is_numeric($length) && (int)$length) {
            $this->first((int)$length);
        }

        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function md5($length = 0)
    {
        $this->string = md5($this->string);
        if (is_numeric($length) && (int)$length) {
            $this->first((int)$length);
        }

        return $this;
    }

    /**
     * @see http://php.net/manual/en/function.nl2br.php
     * @param bool $is_xhtml
     * @return $this
     */
    public function nl2br($is_xhtml = true)
    {
        $this->string = nl2br($this->string, $is_xhtml);

        return $this;
    }

    /**
     * Add the br tag to the end of the string.
     * @param bool $is_xhtml
     * @return $this
     */
    public function br($is_xhtml = true)
    {
        $this->string .= ($is_xhtml ? "<br />" : "<br>");

        return $this;
    }

    /**
     * Call a specified function.
     * @param callable $func
     * @return $this
     */
    public function map(callable $func)
    {
        $result = call_user_func($func, $this->string);
        if (is_string($result)) {
            $this->string = $result;
        }

        return $this;
    }

    /**
     * Convert MODX tag chars to corresponding HTML codes.
     * @param array $chars Chars to encode.
     * @return $this
     */
    public function tag_encode(array $chars = ["[", "]", "{", "}", "`"])
    {
        $this->string = tag_encode($this->string, $chars);

        return $this;
    }

    /**
     * Decode MODX tag chars.
     * @param array $chars
     * @return $this
     */
    public function tag_decode(array $chars = ["[", "]", "{", "}", "`"])
    {

        $this->string = tag_decode($this->string, $chars);

        return $this;
    }

    /**
     * Convert special characters to HTML entities.
     * @see http://php.net/manual/en/function.htmlspecialchars.php
     * @return $this
     */
    public function special_encode()
    {
        $arguments = ['string' => $this->string] + func_get_args();
        $this->string = call_user_func_array('htmlspecialchars', $arguments);
        return $this;
    }

    /**
     * Convert special HTML entities back to characters.
     * @see http://php.net/manual/en/function.htmlspecialchars-decode.php
     * @return $this
     */
    public function special_decode()
    {
        $arguments = ['string' => $this->string] + func_get_args();
        $this->string = call_user_func_array('htmlspecialchars_decode', $arguments);

        return $this;
    }

    /**
     * Get only the string.
     *
     * @param string $string
     * @return string|Str
     */
    public function __get($string)
    {
        if ($string == 'string') {
            return $this->string;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->string;
    }
}