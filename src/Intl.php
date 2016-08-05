<?php

namespace vakata\intl;

use \MessageFormatter as MF;

// https://www.sitepoint.com/localization-demystified-understanding-php-intl/

/**
 * Translator class using \MessageFormatter
 */
class Intl
{
    protected $code = 'en_US';
    protected $data = [];

    /**
     * Create a new instance
     * @method __construct
     * @param  string      $code the locale code to use, defaults to `en_US`
     */
    public function __construct(string $code = 'en_US')
    {
        $this->code = $code;
    }

    /**
     * Get the locale code
     * @method getCode
     * @param  bool|boolean $short if `true` return a short (`en`), otherwise a full code (`en_US`), defaults to `false`
     * @return string the code
     */
    public function getCode(bool $short = false) : string
    {
        return $short ? explode('_', $this->code)[0] : $this->code;
    }

    /**
     * Load all translations from an array
     * @method fromArray
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
     * @method fromFile
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
        if (!$data) {
            throw new IntlException('Invalid file contents');
        }
        return $this->fromArray($data);
    }

    /**
     * Get all translations as an array
     * @method toArray
     * @return array  the translations
     */
    public function toArray() : array
    {
        return $this->data;
    }
    /**
     * Save all translations to a file
     * @method toFile
     * @param  string $location the location to write the file to
     * @param  string $format   the file format (defaults to `'json'`)
     * @return bool             was the file successfully written
     */
    public function toFile(string $location, string $format = 'json') : bool
    {
        switch ($format) {
            case 'json':
                return file_put_contents(
                    $location,
                    json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT)
                ) !== false;
            default:
                throw new IntlException('Invalid file format');
        }
    }

    /**
     * Get a translated string using its key in the translations array.
     * @method get
     * @param  string $key     the translation key
     * @param  array  $replace any variables to replace with
     * @return string          the final translated string
     */
    public function get(string $key, array $replace = []) : string
    {
        $tmp = explode('.', $key);
        $val = $this->data;
        foreach ($tmp as $k) {
            if (!isset($val[$k])) {
                return $key;
            }
            $val = $val[$k];
        }
        $val = MF::formatMessage($this->code, (string)$val, $replace);
        return $val === false ? $key : $val;
    }
    public function __invoke(string $key, array $replace = []) : string
    {
        return $this->get($key, $replace);
    }
}