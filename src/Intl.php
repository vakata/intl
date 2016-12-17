<?php

namespace vakata\intl;

/**
 * Translator class
 */
class Intl
{
    protected $code = 'en_US';
    protected $data = [];

    /**
     * Create a new instance
     * @param  string      $code the locale code to use, defaults to `en_US`
     */
    public function __construct(string $code = 'en_US')
    {
        $this->code = $code;
    }

    public static function flatten($data)
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
     * Get the locale code
     * @param  bool|boolean $short if `true` return a short (`en`), otherwise a full code (`en_US`), defaults to `false`
     * @return string the code
     */
    public function getCode(bool $short = false) : string
    {
        return $short ? explode('_', $this->code)[0] : $this->code;
    }

    /**
     * Load all translations from an array
     * @param  array     $data the translations
     * @return self
     */
    public function fromArray(array $data) : Intl
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
    public function fromFile(string $location, string $format = 'json') : Intl
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
        return $this->fromArray($data);
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
    public function get($key, array $replace = [], string $default = null) : string
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                $tmp = $this->get($k, $replace, chr(0));
                if ($tmp !== chr(0)) {
                    return $tmp;
                }
            }
            return $default;
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
                    return $default;
                }
            }
        }
        if (class_exists('\MessageFormatter')) {
            // https://www.sitepoint.com/localization-demystified-understanding-php-intl/
            $val = \MessageFormatter::formatMessage($this->code, (string)$val, $replace);
        } else {
            // simple brute replacement just in case MessageFormatter is not available
            // does not take care of special escape quotes in ICU!
            if (count($replace)) {
                $val = preg_replace_callback('(\{\s*([a-z0-9_\-]+)[^}]*\})i', function ($matches) use ($replace) {
                    return $replace[$matches[1]] ?? $matches[0];
                }, $val);
            }
        }
        return $val === false ? $default : $val;
    }
    public function __invoke($key, array $replace = [], string $default = null) : string
    {
        return $this->get($key, $replace, $default);
    }
}