<?php

namespace vakata\intl;

/**
 * Translator class
 */
class Intl
{
    protected string $lang = '';
    /** @var array<string,array<string,string>> $data */
    protected array $data = [];
    protected array $used = [];

    public function addTranslations(string $lang, array $data = [], bool $reset = false): self
    {
        if ($reset || !isset($this->data[$lang])) {
            $this->data[$lang] = [];
        }
        $this->data[$lang] = array_replace_recursive($this->data[$lang], $data);
        if ($this->lang === '') {
            $this->setLanguage($lang);
        }
        return $this;
    }
    public function setLanguage(string $lang): self
    {
        if (isset($this->data[$lang])) {
            $this->lang = $lang;
        }
        return $this;
    }
    public function getLanguage(): string
    {
        return $this->lang;
    }
    public function getLanguages(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get the locale code
     * @param  bool|boolean $short if `true` return a short (`en`), otherwise a full code (`en_US`), defaults to `false`
     * @return string the code
     */
    public function getCode(bool $short = false) : string
    {
        return $short ?
            $this->get("_locale.code.short", [], "en") :
            $this->get("_locale.code.long", [], "en_US");
    }

    public function toArray(?string $lang = null) : array
    {
        return $this->data[$lang ?? $this->lang] ?? [];
    }

    /**
     * Get a translated string using its key in the translations array.
     * @param  array|string $key     the translation key, if an array all values will be checked until a match is found
     * @param  array        $replace any variables to replace with
     * @param  string|null  $default optional value to return if key is not found, `null` returns the key
     * @return string       the final translated string
     */
    public function get($key, array $replace = [], ?string $default = null) : string
    {
        if (is_array($key)) {
            $found = false;
            $value = null;
            foreach ($key as $k) {
                $k = strtolower($k);
                $tmp = $this->get($k, $replace, chr(0));
                if ($tmp !== chr(0)) {
                    $found = true;
                    $value = $tmp;
                    break;
                }
            }
            if ($found) {
                foreach ($key as $k) {
                    $k = strtolower($k);
                    if (isset($this->used[$k]) && $this->used[$k] === chr(0)) {
                        $this->used[$k] = $value;
                    }
                }
                return $value;
            }
            $k = strtolower(current($key));
            return $this->used[$k] = $default ?? $k;
        }
        $key = strtolower((string)$key);
        $val = $this->used[$key] = $this->data[$this->lang][$key] ?? $default ?? $key;
        if (count($replace)) {
            $val = preg_replace_callback('(\{\s*([a-z0-9_\-]+)[^}]*\})i', function ($matches) use ($replace) {
                return $replace[$matches[1]] ?? $matches[0];
            }, $val);
        }
        return $val !== false ? $val : ($default ?? $key);
    }
    public function __invoke($key, array $replace = [], ?string $default = null) : string
    {
        return $this->get($key, $replace, $default);
    }
    public function getUsed(bool $reset = true) : array
    {
        $tmp = $this->used;
        if ($reset) {
            $this->used = [];
        }
        return $tmp;
    }
    public function date(string $format = 'short', ?int $timestamp = null) : string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        $format = $this->get("_locale.date." . $format, [], $format);
        if ($format === 'short') {
            $format = 'd.m.Y';
        }
        if ($format === 'long') {
            $format = 'd.m.Y H:i';
        }
        $format = preg_replace('((?<!\\\)(D|l|S|F|M))', '~#\\\$0#~', $format);
        $result = date($format, $timestamp);
        return preg_replace_callback('((~#)(D|l|S|F|M)(#~))', function ($matches) use ($timestamp) {
            switch ($matches[2]) {
                case 'D':
                    // Mon through Sun
                    return $this->get("_locale.days.short." . date("N", $timestamp), [], date("D"));
                case 'l':
                    // Sunday through Saturday
                    return $this->get("_locale.days.long." . date("N", $timestamp), [], date("l"));
                case 'S':
                    // st, nd, rd or th. Works well with j
                    return $this->get(
                        "_locale.days.suffixes." . date("j", $timestamp),
                        [],
                        $this->get("_locale.days.suffixes.default", [], "")
                    );
                case 'F':
                    // January through December
                    return $this->get("_locale.months.long." . date("n", $timestamp), [], date("F"));
                case 'M':
                    // Jan through Dec
                    return $this->get("_locale.months.short." . date("n", $timestamp), [], date("M"));
            }
        }, $result);
    }
    public function number(float $number = 0.0, int $decimals = 0) : string
    {
        return number_format(
            $number,
            $decimals,
            $this->get("_locale.numbers.decimal", [], "."),
            $this->get("_locale.numbers.thousands", [], ",")
        );
    }
}
