<?php

namespace vakata\intl;

/**
 * Translator class
 */
class Intl
{
    protected $data = [];
    protected $used = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Create an instance and load all translations from an array
     * @param  array     $data the translations
     * @return self
     */
    public static function fromArray(array $data) : self
    {
        return (new self())->addArray($data);
    }
    /**
     * Create an instance and load all translations from a file - can be a JSON or INI file.
     * @param  string   $location the file location
     * @param  string   $format   the file format (defaults to 'json')
     * @return self
     */
    public static function fromFile(string $location, string $format = 'json') : self
    {
        return (new self())->addFile($location, $format);
    }
    /**
     * Helper function to flatten current data
     *
     * @param array $data
     * @return array
     */
    protected static function flatten(array $data) : array
    {
        $flat = function ($arr, &$res, $prefix = '', $glue = '.') use (&$flat) {
            if ($prefix === '') {
                $glue = '';
            }
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $flat($v, $res, $prefix . $glue . $k);
                } else {
                    $res[$prefix . $glue . $k] = $v;
                }
            }
        };
        $res = [];
        $flat($data, $res);
        return $res;
    }

    /**
     * Load all translations from an array
     * @param  array     $data the translations
     * @return self
     */
    public function addArray(array $data) : self
    {
        $this->data = array_replace_recursive($this->data, $data);
        return $this;
    }
    /**
     * Load all translations from a file - can be a JSON or INI file.
     * @param  string   $location the file location
     * @param  string   $format   the file format (defaults to 'json')
     * @return self
     */
    public function addFile(string $location, string $format = 'json') : self
    {
        if (!is_file($location)) {
            throw new IntlException('Invalid file');
        }
        $data = [];
        switch (strtolower($format)) {
            case 'ini':
                $data = parse_ini_file($location, true);
                break;
            case 'json':
                $data = @json_decode(file_get_contents($location), true);
                break;
            default:
                throw new IntlException('Invalid file format');
        }
        if (!is_array($data)) {
            throw new IntlException('Invalid file contents');
        }
        return $this->addArray($data);
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

    /**
     * Get all translations as an array
     * @param  bool   $flat  should the resulting array be flat
     * @return array  the translations
     */
    public function toArray(bool $flat = false) : array
    {
        if ($flat) {
            return static::flatten($this->data);
        }
        return $this->data;
    }
    /**
     * Save all translations to a file
     * @param  string $location the location to write the file to
     * @param  string $format   the file format (defaults to `'json'`)
     * @param  bool   $flat     should the resulting array be flat
     * @return bool             was the file successfully written
     */
    public function toFile(string $location, string $format = 'json', bool $flat = false) : bool
    {
        $data = $flat ? static::flatten($this->data) : $this->data;
        switch ($format) {
            case 'json':
                return file_put_contents(
                    $location,
                    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT)
                ) !== false;
            default:
                throw new IntlException('Invalid file format');
        }
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
                $tmp = $this->get($k, $replace, chr(0));
                if ($tmp !== chr(0)) {
                    $found = true;
                    $value = $tmp;
                    break;
                }
            }
            if ($found) {
                foreach ($key as $k) {
                    if (isset($this->used[strtolower($k)]) && $this->used[strtolower($k)] === chr(0)) {
                        $this->used[strtolower($k)] = $value;
                    }
                }
                return $value;
            }
            return $this->used[strtolower(current($key))] = $default === null ? current($key) : $default;
        }
        if ($default === null) {
            $default = $key;
        }
        if (isset($this->data[strtolower($key)])) {
            $val = $this->data[strtolower($key)];
        } else {
            $tmp = explode('.', strtolower($key));
            $val = $this->data;
            foreach ($tmp as $k) {
                $ok = false;
                if (is_array($val)) {
                    foreach ($val as $kk => $vv) {
                        if ($k === strtolower($kk)) {
                            $val = $vv;
                            $ok = true;
                            break;
                        }
                    }
                }
                if (!$ok) {
                    return $this->used[strtolower($key)] = $default;
                }
            }
        }
        $this->used[strtolower($key)] = (string)$val;
        // removed MessageFormatter - rarely available on servers
        // https://www.sitepoint.com/localization-demystified-understanding-php-intl/
        // $val = \MessageFormatter::formatMessage($this->code, (string)$val, $replace);
        // this however does not take care of special escape quotes in ICU!
        if (count($replace)) {
            $val = preg_replace_callback('(\{\s*([a-z0-9_\-]+)[^}]*\})i', function ($matches) use ($replace) {
                return $replace[$matches[1]] ?? $matches[0];
            }, $val);
        }
        return $val === false ? $default : $val;
    }
    public function __invoke($key, array $replace = [], ?string $default = null) : string
    {
        return $this->get($key, $replace, $default);
    }
    public function used() : array
    {
        return $this->used;
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