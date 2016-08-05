# vakata\intl\Intl
Translator class using \MessageFormatter

## Methods

| Name | Description |
|------|-------------|
|[__construct](#vakata\intl\intl__construct)|Create a new instance|
|[getCode](#vakata\intl\intlgetcode)|Get the locale code|
|[fromArray](#vakata\intl\intlfromarray)|Load all translations from an array|
|[fromFile](#vakata\intl\intlfromfile)|Load all translations from a file - can be a JSON or INI file.|
|[toArray](#vakata\intl\intltoarray)|Get all translations as an array|
|[toFile](#vakata\intl\intltofile)|Save all translations to a file|
|[get](#vakata\intl\intlget)|Get a translated string using its key in the translations array.|

---



### vakata\intl\Intl::__construct
Create a new instance  


```php
public function __construct (  
    string $code  
)   
```

|  | Type | Description |
|-----|-----|-----|
| `$code` | `string` | the locale code to use, defaults to `en_US` |

---


### vakata\intl\Intl::getCode
Get the locale code  


```php
public function getCode (  
    bool|boolean $short  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$short` | `bool`, `boolean` | if `true` return a short (`en`), otherwise a full code (`en_US`), defaults to `false` |
|  |  |  |
| `return` | `string` | the code |

---


### vakata\intl\Intl::fromArray
Load all translations from an array  


```php
public function fromArray (  
    array $data  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$data` | `array` | the translations |
|  |  |  |
| `return` | `self` |  |

---


### vakata\intl\Intl::fromFile
Load all translations from a file - can be a JSON or INI file.  


```php
public function fromFile (  
    string $location,  
    string $format  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$location` | `string` | the file location |
| `$format` | `string` | the file format (defaults to 'json') |
|  |  |  |
| `return` | `self` |  |

---


### vakata\intl\Intl::toArray
Get all translations as an array  


```php
public function toArray () : array    
```

|  | Type | Description |
|-----|-----|-----|
|  |  |  |
| `return` | `array` | the translations |

---


### vakata\intl\Intl::toFile
Save all translations to a file  


```php
public function toFile (  
    string $location,  
    string $format  
) : bool    
```

|  | Type | Description |
|-----|-----|-----|
| `$location` | `string` | the location to write the file to |
| `$format` | `string` | the file format (defaults to `'json'`) |
|  |  |  |
| `return` | `bool` | was the file successfully written |

---


### vakata\intl\Intl::get
Get a translated string using its key in the translations array.  


```php
public function get (  
    string $key,  
    array $replace  
) : string    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the translation key |
| `$replace` | `array` | any variables to replace with |
|  |  |  |
| `return` | `string` | the final translated string |

---

