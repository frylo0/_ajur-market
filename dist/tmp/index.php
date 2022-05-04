
<?php
require_once __DIR__ . './../php/orm.config.php';

$global = [];
$link = './tmp';
$ver = '0.0';
$php = './../php';
$attach = './../Attach';
$root = './..';
$mode = 'prod';

?>
<?php $GLOBALS['__jpv_dotWithArrayPrototype'] = function ($base) {
    $arrayPrototype = function ($base, $key) {
        if ($key === 'length') {
            return count($base);
        }
        if ($key === 'forEach') {
            return function ($callback, $userData = null) use (&$base) {
                return array_walk($base, $callback, $userData);
            };
        }
        if ($key === 'map') {
            return function ($callback) use (&$base) {
                return array_map($callback, $base);
            };
        }
        if ($key === 'filter') {
            return function ($callback, $flag = 0) use ($base) {
                return func_num_args() === 1 ? array_filter($base, $callback) : array_filter($base, $callback, $flag);
            };
        }
        if ($key === 'pop') {
            return function () use (&$base) {
                return array_pop($base);
            };
        }
        if ($key === 'shift') {
            return function () use (&$base) {
                return array_shift($base);
            };
        }
        if ($key === 'push') {
            return function ($item) use (&$base) {
                return array_push($base, $item);
            };
        }
        if ($key === 'unshift') {
            return function ($item) use (&$base) {
                return array_unshift($base, $item);
            };
        }
        if ($key === 'indexOf') {
            return function ($item) use (&$base) {
                $search = array_search($item, $base);

                return $search === false ? -1 : $search;
            };
        }
        if ($key === 'slice') {
            return function ($offset, $length = null, $preserveKeys = false) use (&$base) {
                return array_slice($base, $offset, $length, $preserveKeys);
            };
        }
        if ($key === 'splice') {
            return function ($offset, $length = null, $replacements = array()) use (&$base) {
                return array_splice($base, $offset, $length, $replacements);
            };
        }
        if ($key === 'reverse') {
            return function () use (&$base) {
                return array_reverse($base);
            };
        }
        if ($key === 'reduce') {
            return function ($callback, $initial = null) use (&$base) {
                return array_reduce($base, $callback, $initial);
            };
        }
        if ($key === 'join') {
            return function ($glue) use (&$base) {
                return implode($glue, $base);
            };
        }
        if ($key === 'sort') {
            return function ($callback = null) use (&$base) {
                return $callback ? usort($base, $callback) : sort($base);
            };
        }

        return null;
    };

    $getFromArray = function ($base, $key) use ($arrayPrototype) {
        return isset($base[$key])
            ? $base[$key]
            : $arrayPrototype($base, $key);
    };

    $getCallable = function ($base, $key) use ($getFromArray) {
        if (is_callable(array($base, $key))) {
            return new class(array($base, $key)) extends \ArrayObject
            {
                public function getValue()
                {
                    if ($this->isArrayAccessible()) {
                        return $this[0][$this[1]];
                    }

                    return $this[0]->{$this[1]} ?? null;
                }

                public function setValue($value)
                {
                    if ($this->isArrayAccessible()) {
                        $this[0][$this[1]] = $value;

                        return;
                    }

                    $this[0]->{$this[1]} = $value;
                }

                public function getCallable()
                {
                    return $this->getArrayCopy();
                }

                public function __isset($name)
                {
                    $value = $this->getValue();

                    if ((is_array($value) || $value instanceof ArrayAccess) && isset($value[$name])) {
                        return true;
                    }

                    return is_object($value) && isset($value->$name);
                }

                public function __get($name)
                {
                    return new self(array($this->getValue(), $name));
                }

                public function __set($name, $value)
                {
                    $value = $this->getValue();

                    if (is_array($value)) {
                        $value[$name] = $value;
                        $this->setValue($value);

                        return;
                    }

                    $value->$name = $value;
                }

                public function __toString()
                {
                    return (string) $this->getValue();
                }

                public function __toBoolean()
                {
                    $value = $this->getValue();

                    if (method_exists($value, '__toBoolean')) {
                        return $value->__toBoolean();
                    }

                    return !!$value;
                }

                public function __invoke(...$arguments)
                {
                    return call_user_func_array($this->getCallable(), $arguments);
                }

                private function isArrayAccessible()
                {
                    return is_array($this[0]) || $this[0] instanceof ArrayAccess && !isset($this[0]->{$this[1]});
                }
            };
        }
        if ($base instanceof \ArrayAccess) {
            return $getFromArray($base, $key);
        }
    };

    $getRegExp = function ($value) {
        return is_object($value) && isset($value->isRegularExpression) && $value->isRegularExpression ? $value->regExp . $value->flags : null;
    };

    $fallbackDot = function ($base, $key) use ($getCallable, $getRegExp) {
        if (is_string($base)) {
            if (preg_match('/^[-+]?\d+$/', strval($key))) {
                return substr($base, intval($key), 1);
            }
            if ($key === 'length') {
                return strlen($base);
            }
            if ($key === 'substr' || $key === 'slice') {
                return function ($start, $length = null) use ($base) {
                    return func_num_args() === 1 ? substr($base, $start) : substr($base, $start, $length);
                };
            }
            if ($key === 'charAt') {
                return function ($pos) use ($base) {
                    return substr($base, $pos, 1);
                };
            }
            if ($key === 'indexOf') {
                return function ($needle) use ($base) {
                    $pos = strpos($base, $needle);

                    return $pos === false ? -1 : $pos;
                };
            }
            if ($key === 'toUpperCase') {
                return function () use ($base) {
                    return strtoupper($base);
                };
            }
            if ($key === 'toLowerCase') {
                return function () use ($base) {
                    return strtolower($base);
                };
            }
            if ($key === 'match') {
                return function ($search) use ($base, $getRegExp) {
                    $regExp = $getRegExp($search);
                    $search = $regExp ? $regExp : (is_string($search) ? '/' . preg_quote($search, '/') . '/' : strval($search));

                    return preg_match($search, $base);
                };
            }
            if ($key === 'split') {
                return function ($delimiter) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($delimiter)) {
                        return preg_split($regExp, $base);
                    }

                    return explode($delimiter, $base);
                };
            }
            if ($key === 'replace') {
                return function ($from, $to) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($from)) {
                        return preg_replace($regExp, $to, $base);
                    }

                    return str_replace($from, $to, $base);
                };
            }
        }

        return $getCallable($base, $key);
    };

    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? $getFromArray($base, $key)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : $getCallable($base, $key)
                        )
                    )
                )
                : $fallbackDot($base, $key)
            );
    }

    return $base;
};;
$GLOBALS['__jpv_dotWithArrayPrototype_with_ref'] = function (&$base) {
    $arrayPrototype = function (&$base, $key) {
        if ($key === 'length') {
            return count($base);
        }
        if ($key === 'forEach') {
            return function ($callback, $userData = null) use (&$base) {
                return array_walk($base, $callback, $userData);
            };
        }
        if ($key === 'map') {
            return function ($callback) use (&$base) {
                return array_map($callback, $base);
            };
        }
        if ($key === 'filter') {
            return function ($callback, $flag = 0) use ($base) {
                return func_num_args() === 1 ? array_filter($base, $callback) : array_filter($base, $callback, $flag);
            };
        }
        if ($key === 'pop') {
            return function () use (&$base) {
                return array_pop($base);
            };
        }
        if ($key === 'shift') {
            return function () use (&$base) {
                return array_shift($base);
            };
        }
        if ($key === 'push') {
            return function ($item) use (&$base) {
                return array_push($base, $item);
            };
        }
        if ($key === 'unshift') {
            return function ($item) use (&$base) {
                return array_unshift($base, $item);
            };
        }
        if ($key === 'indexOf') {
            return function ($item) use (&$base) {
                $search = array_search($item, $base);

                return $search === false ? -1 : $search;
            };
        }
        if ($key === 'slice') {
            return function ($offset, $length = null, $preserveKeys = false) use (&$base) {
                return array_slice($base, $offset, $length, $preserveKeys);
            };
        }
        if ($key === 'splice') {
            return function ($offset, $length = null, $replacements = array()) use (&$base) {
                return array_splice($base, $offset, $length, $replacements);
            };
        }
        if ($key === 'reverse') {
            return function () use (&$base) {
                return array_reverse($base);
            };
        }
        if ($key === 'reduce') {
            return function ($callback, $initial = null) use (&$base) {
                return array_reduce($base, $callback, $initial);
            };
        }
        if ($key === 'join') {
            return function ($glue) use (&$base) {
                return implode($glue, $base);
            };
        }
        if ($key === 'sort') {
            return function ($callback = null) use (&$base) {
                return $callback ? usort($base, $callback) : sort($base);
            };
        }

        return null;
    };

    $getFromArray = function (&$base, $key) use ($arrayPrototype) {
        return isset($base[$key])
            ? $base[$key]
            : $arrayPrototype($base, $key);
    };

    $getCallable = function (&$base, $key) use ($getFromArray) {
        if (is_callable(array($base, $key))) {
            return new class(array($base, $key)) extends \ArrayObject
            {
                public function getValue()
                {
                    if ($this->isArrayAccessible()) {
                        return $this[0][$this[1]];
                    }

                    return $this[0]->{$this[1]} ?? null;
                }

                public function setValue($value)
                {
                    if ($this->isArrayAccessible()) {
                        $this[0][$this[1]] = $value;

                        return;
                    }

                    $this[0]->{$this[1]} = $value;
                }

                public function getCallable()
                {
                    return $this->getArrayCopy();
                }

                public function __isset($name)
                {
                    $value = $this->getValue();

                    if ((is_array($value) || $value instanceof ArrayAccess) && isset($value[$name])) {
                        return true;
                    }

                    return is_object($value) && isset($value->$name);
                }

                public function __get($name)
                {
                    return new self(array($this->getValue(), $name));
                }

                public function __set($name, $value)
                {
                    $value = $this->getValue();

                    if (is_array($value)) {
                        $value[$name] = $value;
                        $this->setValue($value);

                        return;
                    }

                    $value->$name = $value;
                }

                public function __toString()
                {
                    return (string) $this->getValue();
                }

                public function __toBoolean()
                {
                    $value = $this->getValue();

                    if (method_exists($value, '__toBoolean')) {
                        return $value->__toBoolean();
                    }

                    return !!$value;
                }

                public function __invoke(...$arguments)
                {
                    return call_user_func_array($this->getCallable(), $arguments);
                }

                private function isArrayAccessible()
                {
                    return is_array($this[0]) || $this[0] instanceof ArrayAccess && !isset($this[0]->{$this[1]});
                }
            };
        }
        if ($base instanceof \ArrayAccess) {
            return $getFromArray($base, $key);
        }
    };

    $getRegExp = function ($value) {
        return is_object($value) && isset($value->isRegularExpression) && $value->isRegularExpression ? $value->regExp . $value->flags : null;
    };

    $fallbackDot = function (&$base, $key) use ($getCallable, $getRegExp) {
        if (is_string($base)) {
            if (preg_match('/^[-+]?\d+$/', strval($key))) {
                return substr($base, intval($key), 1);
            }
            if ($key === 'length') {
                return strlen($base);
            }
            if ($key === 'substr' || $key === 'slice') {
                return function ($start, $length = null) use ($base) {
                    return func_num_args() === 1 ? substr($base, $start) : substr($base, $start, $length);
                };
            }
            if ($key === 'charAt') {
                return function ($pos) use ($base) {
                    return substr($base, $pos, 1);
                };
            }
            if ($key === 'indexOf') {
                return function ($needle) use ($base) {
                    $pos = strpos($base, $needle);

                    return $pos === false ? -1 : $pos;
                };
            }
            if ($key === 'toUpperCase') {
                return function () use ($base) {
                    return strtoupper($base);
                };
            }
            if ($key === 'toLowerCase') {
                return function () use ($base) {
                    return strtolower($base);
                };
            }
            if ($key === 'match') {
                return function ($search) use ($base, $getRegExp) {
                    $regExp = $getRegExp($search);
                    $search = $regExp ? $regExp : (is_string($search) ? '/' . preg_quote($search, '/') . '/' : strval($search));

                    return preg_match($search, $base);
                };
            }
            if ($key === 'split') {
                return function ($delimiter) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($delimiter)) {
                        return preg_split($regExp, $base);
                    }

                    return explode($delimiter, $base);
                };
            }
            if ($key === 'replace') {
                return function ($from, $to) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($from)) {
                        return preg_replace($regExp, $to, $base);
                    }

                    return str_replace($from, $to, $base);
                };
            }
        }

        return $getCallable($base, $key);
    };

    $crawler = &$base;
    $result = $base;
    foreach (array_slice(func_get_args(), 1) as $key) {
        $result = is_array($crawler)
            ? $getFromArray($crawler, $key)
            : (is_object($crawler)
                ? (isset($crawler->$key)
                    ? $crawler->$key
                    : (method_exists($crawler, $method = "get" . ucfirst($key))
                        ? $crawler->$method()
                        : (method_exists($crawler, $key)
                            ? array($crawler, $key)
                            : $getCallable($crawler, $key)
                        )
                    )
                )
                : $fallbackDot($crawler, $key)
            );
        $crawler = &$result;
    }

    return $result;
};;
 ?><?php
$pug_vars = [];
foreach (array_keys(get_defined_vars()) as $__pug_key) {
    $pug_vars[$__pug_key] = &$$__pug_key;
}
?><?php $pugModule = [
  'Phug\\Formatter\\Format\\BasicFormat::dependencies_storage' => 'pugModule',
  'Phug\\Formatter\\Format\\BasicFormat::helper_prefix' => 'Phug\\Formatter\\Format\\BasicFormat::',
  'Phug\\Formatter\\Format\\BasicFormat::get_helper' => function ($name) use (&$pugModule) {
    $dependenciesStorage = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];
    $prefix = $pugModule['Phug\\Formatter\\Format\\BasicFormat::helper_prefix'];
    $format = $pugModule['Phug\\Formatter\\Format\\BasicFormat::dependencies_storage'];

                            if (!isset($$dependenciesStorage)) {
                                return $format->getHelper($name);
                            }

                            $storage = $$dependenciesStorage;

                            if (!isset($storage[$prefix.$name]) &&
                                !(is_array($storage) && array_key_exists($prefix.$name, $storage))
                            ) {
                                throw new \Exception(
                                    var_export($name, true).
                                    ' dependency not found in the namespace: '.
                                    var_export($prefix, true)
                                );
                            }

                            return $storage[$prefix.$name];
                        },
  'Phug\\Formatter\\Format\\BasicFormat::pattern' => function ($pattern) use (&$pugModule) {

                    $args = func_get_args();
                    $function = 'sprintf';
                    if (is_callable($pattern)) {
                        $function = $pattern;
                        $args = array_slice($args, 1);
                    }

                    return call_user_func_array($function, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape' => 'htmlspecialchars',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.html_text_escape'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments' => array (
  0 => 'class',
  1 => 'style',
),
  'Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern' => ' %s="%s"',
  'Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern' => function () use (&$pugModule) {
    $proceed = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::patterns.boolean_attribute_pattern'];

                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignments' => function (&$attributes, $name, $value) use (&$pugModule) {
    $availableAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::available_attribute_assignments'];
    $getHelper = $pugModule['Phug\\Formatter\\Format\\BasicFormat::get_helper'];

                    if (!in_array($name, $availableAssignments)) {
                        return $value;
                    }

                    $helper = $getHelper($name.'_attribute_assignment');

                    return $helper($attributes, $value);
                },
  'Phug\\Formatter\\Format\\BasicFormat::attribute_assignment' => function (&$attributes, $name, $value) use (&$pugModule) {
    $attributeAssignments = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignments'];

                    if (isset($name) && $name !== '') {
                        $result = $attributeAssignments($attributes, $name, $value);
                        if (($result !== null && $result !== false && ($result !== '' || $name !== 'class'))) {
                            $attributes[$name] = $result;
                        }
                    }
                },
  'Phug\\Formatter\\Format\\BasicFormat::merge_attributes' => function () use (&$pugModule) {
    $attributeAssignment = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attribute_assignment'];

                    $attributes = [];
                    foreach (array_filter(func_get_args(), 'is_array') as $input) {
                        foreach ($input as $name => $value) {
                            $attributeAssignment($attributes, $name, $value);
                        }
                    }

                    return $attributes;
                },
  'Phug\\Formatter\\Format\\BasicFormat::array_escape' => function ($name, $input) use (&$pugModule) {
    $arrayEscape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape'];
    $escape = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.html_text_escape'];

                        if (is_array($input) && in_array(strtolower($name), ['class', 'style'])) {
                            $result = [];
                            foreach ($input as $key => $value) {
                                $result[$escape($key)] = $arrayEscape($name, $value);
                            }

                            return $result;
                        }
                        if (is_array($input) || is_object($input) && !method_exists($input, '__toString')) {
                            return $escape(json_encode($input));
                        }
                        if (is_string($input)) {
                            return $escape($input);
                        }

                        return $input;
                    },
  'Phug\\Formatter\\Format\\BasicFormat::attributes_mapping' => array (
),
  'Phug\\Formatter\\Format\\BasicFormat::attributes_assignment' => function () use (&$pugModule) {
    $attrMapping = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_mapping'];
    $mergeAttr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::merge_attributes'];
    $pattern = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern'];
    $attr = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.attribute_pattern'];
    $bool = $pugModule['Phug\\Formatter\\Format\\BasicFormat::pattern.boolean_attribute_pattern'];

                        $attributes = call_user_func_array($mergeAttr, func_get_args());
                        $code = '';
                        foreach ($attributes as $originalName => $value) {
                            if ($value !== null && $value !== false && ($value !== '' || $originalName !== 'class')) {
                                $name = isset($attrMapping[$originalName])
                                    ? $attrMapping[$originalName]
                                    : $originalName;
                                if ($value === true) {
                                    $code .= $pattern($bool, $name, $name);

                                    continue;
                                }
                                if (is_array($value) || is_object($value) &&
                                    !method_exists($value, '__toString')) {
                                    $value = json_encode($value);
                                }

                                $code .= $pattern($attr, $name, $value);
                            }
                        }

                        return $code;
                    },
  'Phug\\Formatter\\Format\\BasicFormat::class_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            $split = function ($input) {
                return preg_split('/(?<![\[\{\<\=\%])\s+(?![\]\}\>\=\%])/', strval($input));
            };
            $classes = isset($attributes['class']) ? array_filter($split($attributes['class'])) : [];
            foreach ((array) $value as $key => $input) {
                if (!is_string($input) && is_string($key)) {
                    if (!$input) {
                        continue;
                    }

                    $input = $key;
                }
                foreach ($split($input) as $class) {
                    if (!in_array($class, $classes)) {
                        $classes[] = $class;
                    }
                }
            }

            return implode(' ', $classes);
        },
  'Phug\\Formatter\\Format\\BasicFormat::style_attribute_assignment' => function (&$attributes, $value) use (&$pugModule) {

            if (is_string($value) && mb_substr($value, 0, 7) === '{&quot;') {
                $value = json_decode(htmlspecialchars_decode($value));
            }
            $styles = isset($attributes['style']) ? array_filter(explode(';', $attributes['style'])) : [];
            foreach ((array) $value as $propertyName => $propertyValue) {
                if (!is_int($propertyName)) {
                    $propertyValue = $propertyName.':'.$propertyValue;
                }
                $styles[] = $propertyValue;
            }

            return implode(';', $styles);
        },
]; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['logo'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'mode', 'normal']];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    if (method_exists($_pug_temp = (isset($mode) ? $mode : null) == 'normal', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'logo'], ['fill' => 'none'], ['version' => '1.1'], ['viewBox' => '0 0 37.335 37.048'], ['xmlns' => 'http://www.w3.org/2000/svg'], ['xmlns:cc' => 'http://creativecommons.org/ns#'], ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'], ['xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>><metadata><rdf:rdf><cc:work<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:about' => ''])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><dc:format>image/svg+xml</dc:format><dc:type<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:resource' => 'http://purl.org/dc/dcmitype/StillImage'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></dc:type><dc:title></dc:title></cc:work></rdf:rdf></metadata><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['clip-rule' => 'evenodd'], ['fill-rule' => 'evenodd'], ['id' => 'logo-pattern'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#fad958'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c-2.197 3.009-6.0505 4.5835-9.9102 3.6924-3.8791-0.8956-6.6608-4.0268-7.2983-7.7185 0 0-1.5041-0.8915-2.3645-1.601-0.63752-0.5257-1.5345-1.4567-1.5345-1.4567-0.72173 6.6658 3.6385 12.969 10.334 14.515 6.6944 1.5455 13.375-2.2067 15.65-8.512 0 0-1.215 0.4355-2.0164 0.6328-1.0978 0.2702-2.8601 0.448-2.8601 0.448z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm15.591 4.086c0.4307 0.09945 0.8479 0.22646 1.2502 0.37874 0 0 0.9664-0.59505 1.6259-0.89616 1.105-0.50449 2.97-0.9241 2.97-0.9241-1.4412-1.0704-3.1219-1.8676-4.983-2.2973-7.2271-1.6685-14.439 2.8376-16.107 10.065-1.0002 4.3323 0.21845 8.659 2.912 11.789 0 0 0.16213-1.4581 0.4234-2.3555 0.28755-0.9877 1.0244-2.4268 1.0244-2.4268-0.85006-1.8574-1.116-3.9997-0.62094-6.144 1.1918-5.1622 6.3428-8.3809 11.505-7.1891z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm33.249 18.008c-0.7059 3.0577-2.8008 5.4334-5.4643 6.6148l0.2556 4.0061c4.3363-1.4089 7.8472-4.9916 8.9475-9.7577 1.6686-7.2272-2.8376-14.439-10.065-16.107-2.3067-0.53256-4.6119-0.43607-6.7357 0.17716l2.4253 3.4002c1.1183-0.15182 2.2825-0.10744 3.4472 0.16146 5.1623 1.1918 8.381 6.3428 7.1892 11.505z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#e6a503'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c0.7414-1.0154 1.2942-2.1942 1.5949-3.4968 0.7059-3.0576-0.1355-6.1113-2.0116-8.3406l1.9859-3.4887c3.2798 3.1672 4.8649 7.9264 3.7645 12.692-0.1167 0.5057-0.2606 0.9981-0.4298 1.476 0 0-1.4754 0.7737-2.504 0.9972-0.9179 0.1994-2.3999 0.1604-2.3999 0.1604zm-12.318-18.286c-4.1383 1.5003-7.4559 5.0023-8.5198 9.6106-0.1227 0.5315-0.21202 1.0629-0.2693 1.5921 0 0 1.0053 1.2847 1.8221 1.9169 0.74283 0.575 2.0867 1.1963 2.0867 1.1963-0.2213-1.2377-0.2014-2.5395 0.0994-3.8421 0.7059-3.0576 2.8007-5.4334 5.4642-6.6147z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm11.275 22.78c1.5374 0.3549 3.0738 0.3187 4.505-0.0436l3.0889 2.9782c-2.5667 1.1384-5.5106 1.4845-8.4571 0.8042-2.9002-0.6695-5.3623-2.2317-7.1629-4.3293 0 0 0.17885-1.6924 0.51981-2.7177 0.27813-0.8364 0.93808-2.0529 0.93808-2.0529 1.1965 2.6143 3.5502 4.6643 6.5683 5.3611zm5.5291-18.329c3.9644 1.4822 6.4861 5.4149 6.2022 9.6172l3.3189 3.1291c0.0711-0.2442 0.1357-0.4919 0.1937-0.7429 1.2388-5.3661-0.9263-10.723-5.0819-13.81 0 0-1.5708 0.26125-2.5098 0.63653-0.879 0.35133-2.1231 1.1698-2.1231 1.1698z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm21.744 25.197c3.0276 0.699 6.0512-0.1191 8.2747-1.9565 0 0 0.0465 1.394-0.0778 2.2736-0.152 1.0748-0.7424 2.6784-0.7424 2.6784-2.5336 1.0897-5.4244 1.4113-8.3177 0.7433-3.1162-0.7194-5.7265-2.4693-7.5541-4.8067l4.5242-0.8327c1.1004 0.8956 2.4174 1.5599 3.8931 1.9006zm-6.8636-12.611c1.48-4.1062 5.5756-6.6975 9.9104-6.2886 0 0-0.7462-1.2806-1.3767-1.9804-0.6768-0.75122-1.9768-1.6724-1.9768-1.6724-5.0879 0.9523-9.3824 4.8184-10.621 10.184z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#b87a00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm18.754 14.268c1.8704 0.4318 3.4856 1.3833 4.7284 2.6668l2.2902-3.1289c-1.6705-1.5657-3.764-2.7247-6.1554-3.2768-2.904-0.6704-5.8054-0.3439-8.3457 0.7554 0 0-0.4695 1.3778-0.628 2.2924-0.1773 1.0231-0.1709 2.6532-0.1709 2.6532 2.224-1.8414 5.2508-2.6618 8.2814-1.9621z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm22.78 15.591c-0.8511 3.6865-3.7212 6.3819-7.1727 7.1876l1.5402 3.5633c4.5302-1.3081 8.2354-4.9673 9.3714-9.8877 0.1231-0.5331 0.2125-1.0662 0.2698-1.597 0 0-0.6535-0.8835-1.1611-1.3654-0.9216-0.8751-2.7482-1.7428-2.7482-1.7428 0.2213 1.2377 0.2013 2.5395-0.0994 3.842z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm20.531 24.832c-4.4411-1.6602-7.0718-6.3955-5.9763-11.14l-3.7389-0.8631c-1.2388 5.366 0.9263 10.723 5.0819 13.81 0 0 1.5708-0.2613 2.5098-0.6365 0.8792-0.3513 2.1235-1.1697 2.1235-1.1697z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g></g></svg><?php } elseif (method_exists($_pug_temp = (isset($mode) ? $mode : null) == 'full', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'logo'], ['fill' => 'none'], ['version' => '1.1'], ['viewBox' => '0 0 122.63 37.048'], ['xmlns' => 'http://www.w3.org/2000/svg'], ['xmlns:cc' => 'http://creativecommons.org/ns#'], ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'], ['xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>><metadata><rdf:rdf><cc:work<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:about' => ''])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><dc:format>image/svg+xml</dc:format><dc:type<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:resource' => 'http://purl.org/dc/dcmitype/StillImage'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></dc:type><dc:title></dc:title></cc:work></rdf:rdf></metadata><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['clip-rule' => 'evenodd'], ['fill-rule' => 'evenodd'], ['id' => 'logo-pattern'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#fad958'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c-2.197 3.009-6.0505 4.5835-9.9102 3.6924-3.8791-0.8956-6.6608-4.0268-7.2983-7.7185 0 0-1.5041-0.8915-2.3645-1.601-0.63752-0.5257-1.5345-1.4567-1.5345-1.4567-0.72173 6.6658 3.6385 12.969 10.334 14.515 6.6944 1.5455 13.375-2.2067 15.65-8.512 0 0-1.215 0.4355-2.0164 0.6328-1.0978 0.2702-2.8601 0.448-2.8601 0.448z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm15.591 4.086c0.4307 0.09945 0.8479 0.22646 1.2502 0.37874 0 0 0.9664-0.59505 1.6259-0.89616 1.105-0.50449 2.97-0.9241 2.97-0.9241-1.4412-1.0704-3.1219-1.8676-4.983-2.2973-7.2271-1.6685-14.439 2.8376-16.107 10.065-1.0002 4.3323 0.21845 8.659 2.912 11.789 0 0 0.16213-1.4581 0.4234-2.3555 0.28755-0.9877 1.0244-2.4268 1.0244-2.4268-0.85006-1.8574-1.116-3.9997-0.62094-6.144 1.1918-5.1622 6.3428-8.3809 11.505-7.1891z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm33.249 18.008c-0.7059 3.0577-2.8008 5.4334-5.4643 6.6148l0.2556 4.0061c4.3363-1.4089 7.8472-4.9916 8.9475-9.7577 1.6686-7.2272-2.8376-14.439-10.065-16.107-2.3067-0.53256-4.6119-0.43607-6.7357 0.17716l2.4253 3.4002c1.1183-0.15182 2.2825-0.10744 3.4472 0.16146 5.1623 1.1918 8.381 6.3428 7.1892 11.505z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#e6a503'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c0.7414-1.0154 1.2942-2.1942 1.5949-3.4968 0.7059-3.0576-0.1355-6.1113-2.0116-8.3406l1.9859-3.4887c3.2798 3.1672 4.8649 7.9264 3.7645 12.692-0.1167 0.5057-0.2606 0.9981-0.4298 1.476 0 0-1.4754 0.7737-2.504 0.9972-0.9179 0.1994-2.3999 0.1604-2.3999 0.1604zm-12.318-18.286c-4.1383 1.5003-7.4559 5.0023-8.5198 9.6106-0.1227 0.5315-0.21202 1.0629-0.2693 1.5921 0 0 1.0053 1.2847 1.8221 1.9169 0.74283 0.575 2.0867 1.1963 2.0867 1.1963-0.2213-1.2377-0.2014-2.5395 0.0994-3.8421 0.7059-3.0576 2.8007-5.4334 5.4642-6.6147z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm11.275 22.78c1.5374 0.3549 3.0738 0.3187 4.505-0.0436l3.0889 2.9782c-2.5667 1.1384-5.5106 1.4845-8.4571 0.8042-2.9002-0.6695-5.3623-2.2317-7.1629-4.3293 0 0 0.17885-1.6924 0.51981-2.7177 0.27813-0.8364 0.93808-2.0529 0.93808-2.0529 1.1965 2.6143 3.5502 4.6643 6.5683 5.3611zm5.5291-18.329c3.9644 1.4822 6.4861 5.4149 6.2022 9.6172l3.3189 3.1291c0.0711-0.2442 0.1357-0.4919 0.1937-0.7429 1.2388-5.3661-0.9263-10.723-5.0819-13.81 0 0-1.5708 0.26125-2.5098 0.63653-0.879 0.35133-2.1231 1.1698-2.1231 1.1698z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm21.744 25.197c3.0276 0.699 6.0512-0.1191 8.2747-1.9565 0 0 0.0465 1.394-0.0778 2.2736-0.152 1.0748-0.7424 2.6784-0.7424 2.6784-2.5336 1.0897-5.4244 1.4113-8.3177 0.7433-3.1162-0.7194-5.7265-2.4693-7.5541-4.8067l4.5242-0.8327c1.1004 0.8956 2.4174 1.5599 3.8931 1.9006zm-6.8636-12.611c1.48-4.1062 5.5756-6.6975 9.9104-6.2886 0 0-0.7462-1.2806-1.3767-1.9804-0.6768-0.75122-1.9768-1.6724-1.9768-1.6724-5.0879 0.9523-9.3824 4.8184-10.621 10.184z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#b87a00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm18.754 14.268c1.8704 0.4318 3.4856 1.3833 4.7284 2.6668l2.2902-3.1289c-1.6705-1.5657-3.764-2.7247-6.1554-3.2768-2.904-0.6704-5.8054-0.3439-8.3457 0.7554 0 0-0.4695 1.3778-0.628 2.2924-0.1773 1.0231-0.1709 2.6532-0.1709 2.6532 2.224-1.8414 5.2508-2.6618 8.2814-1.9621z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm22.78 15.591c-0.8511 3.6865-3.7212 6.3819-7.1727 7.1876l1.5402 3.5633c4.5302-1.3081 8.2354-4.9673 9.3714-9.8877 0.1231-0.5331 0.2125-1.0662 0.2698-1.597 0 0-0.6535-0.8835-1.1611-1.3654-0.9216-0.8751-2.7482-1.7428-2.7482-1.7428 0.2213 1.2377 0.2013 2.5395-0.0994 3.842z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm20.531 24.832c-4.4411-1.6602-7.0718-6.3955-5.9763-11.14l-3.7389-0.8631c-1.2388 5.366 0.9263 10.723 5.0819 13.81 0 0 1.5708-0.2613 2.5098-0.6365 0.8792-0.3513 2.1235-1.1697 2.1235-1.1697z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g></g><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm56.376 17.173h-6.3421l-1.0681 2.1527h-3.4277l5.953-11.376h3.4277l5.953 11.376h-3.4277zm-5.241-2.2189h4.1564l-2.0699-4.1895zm25.773 0.1656c0 0.7562-0.1242 1.4075-0.3726 1.954-0.2428 0.5464-0.6016 0.9963-1.0763 1.3495-0.4692 0.3533-1.046 0.6155-1.7304 0.7866-0.6845 0.1656-1.4655 0.2484-2.3431 0.2484-0.414 0-0.839-0.0221-1.2751-0.0663-0.436-0.0386-0.8555-0.0883-1.2585-0.149-0.4029-0.0607-0.7755-0.1242-1.1177-0.1904-0.3367-0.0718-0.6182-0.138-0.8445-0.1987v-2.6661c0.2318 0.0939 0.4857 0.1905 0.7617 0.2898 0.276 0.0939 0.5823 0.1794 0.919 0.2567 0.3422 0.0773 0.7176 0.1408 1.126 0.1904 0.414 0.0497 0.8694 0.0745 1.3662 0.0745 0.5354 0 0.9797-0.0579 1.333-0.1738 0.3587-0.1215 0.6458-0.287 0.861-0.4968 0.2153-0.2153 0.3671-0.4664 0.4554-0.7534 0.0883-0.2871 0.1325-0.6017 0.1325-0.9439v-6.6816h3.0634zm22.85 0.3726c0 0.5685-0.141 1.1039-0.423 1.6062-0.281 0.5023-0.703 0.9411-1.266 1.3164-0.563 0.3754-1.267 0.6735-2.112 0.8942-0.8387 0.2153-1.8212 0.3229-2.9472 0.3229s-2.114-0.1076-2.9641-0.3229c-0.8445-0.2207-1.5483-0.5188-2.1113-0.8942-0.563-0.3753-0.9852-0.8141-1.2667-1.3164s-0.4223-1.0377-0.4223-1.6062v-7.5427h3.0634v6.4415c0 0.3808 0.047 0.7424 0.1408 1.0846 0.0993 0.3367 0.2815 0.632 0.5464 0.8859 0.2705 0.2539 0.6458 0.4554 1.1261 0.6044 0.4857 0.1491 1.1149 0.2236 1.8877 0.2236 0.7672 0 1.391-0.0745 1.8712-0.2236 0.48-0.149 0.853-0.3505 1.118-0.6044 0.27-0.2539 0.452-0.5492 0.546-0.8859 0.094-0.3422 0.141-0.7038 0.141-1.0846v-6.4415h3.072zm12.484 3.8334h-3.072v-11.376h7.7c0.828 0 1.529 0.0856 2.103 0.2567 0.579 0.1711 1.049 0.4139 1.407 0.7286 0.365 0.3146 0.627 0.6954 0.787 1.1425 0.165 0.4416 0.248 0.9356 0.248 1.4821 0 0.4802-0.069 0.8997-0.207 1.2585-0.132 0.3588-0.314 0.6679-0.546 0.9273-0.226 0.2539-0.491 0.4692-0.795 0.6458s-0.624 0.3229-0.96 0.4388l3.725 4.4958h-3.593l-3.444-4.1895h-3.353zm6.068-7.7828c0-0.2208-0.03-0.4084-0.091-0.563-0.055-0.1545-0.154-0.2787-0.298-0.3726-0.143-0.0993-0.336-0.1711-0.579-0.2152-0.238-0.0442-0.536-0.0663-0.894-0.0663h-4.206v2.4342h4.206c0.358 0 0.656-0.0221 0.894-0.0662 0.243-0.0442 0.436-0.1132 0.579-0.207 0.144-0.0994 0.243-0.2263 0.298-0.3809 0.061-0.1545 0.091-0.3422 0.091-0.563z'], ['fill' => '#000'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm51.212 28.375c0.1198 0 0.3902-0.0541 0.3902-0.2126v-4.9997c0-0.17-0.2627-0.2164-0.3941-0.2164-0.1159 0-0.3554 0.0425-0.4057 0.1623l-1.7696 4.3275-1.9357-4.3275c-0.0619-0.1352-0.2744-0.1623-0.4096-0.1623-0.1236 0-0.4521 0.0541-0.4521 0.2164v5.0616c0 0.1236 0.2164 0.1507 0.3053 0.1507 0.0888 0 0.3052-0.0271 0.3052-0.1507v-3.7131l1.6614 3.7015c0.058 0.1275 0.3014 0.1623 0.4251 0.1623 0.1236 0 0.3632-0.031 0.4173-0.1662l1.4682-3.6049v3.5585c0 0.1585 0.2705 0.2126 0.3941 0.2126zm14.751 0c0.1236 0 0.4134-0.0503 0.4134-0.2087 0-0.0154-0.0039-0.0309-0.0116-0.0502l-2.2101-5.0075c-0.0579-0.1275-0.2704-0.1623-0.4018-0.1623-0.1237 0-0.3864 0.0309-0.4405 0.1584l-2.1946 5.0809c-0.0039 0.0116-0.0078 0.0232-0.0078 0.0348 0 0.0966 0.1585 0.1546 0.313 0.1546 0.1275 0 0.255-0.0387 0.2898-0.1198l0.7109-1.6499h2.4072l0.7071 1.6035c0.054 0.1237 0.2704 0.1662 0.425 0.1662zm-1.3253-2.2024h-2.0246l1.0007-2.3221zm15.62 2.2024c0.1043 0 0.2125-0.0232 0.2898-0.0735 0.0695-0.0425 0.1005-0.0927 0.1005-0.1429 0-0.0618-0.0464-0.1198-0.1275-0.1623-0.4251-0.2125-0.6492-0.7303-0.8308-1.136-0.1584-0.3477-0.3284-0.7263-0.6182-0.9929 0.6917-0.1353 1.2983-0.6608 1.2983-1.3949 0-0.8925-0.8655-1.4334-1.704-1.4334h-2.3917c-0.1236 0-0.3902 0.054-0.3902 0.2163v4.9032c0 0.1623 0.2666 0.2164 0.3902 0.2164 0.1276 0 0.3942-0.0541 0.3942-0.2164v-2.2487h1.3098c0.5216 0 0.7766 0.6645 0.9505 1.0548 0.2357 0.5216 0.4791 1.0586 1.0664 1.3523 0.0734 0.0386 0.17 0.058 0.2627 0.058zm-1.5919-2.8979h-1.9975v-2.0053h1.9975c0.5216 0 0.9196 0.5139 0.9196 1.0007 0 0.4869-0.398 1.0046-0.9196 1.0046zm15.535 2.8979c0.0579 0 0.112-0.0078 0.1584-0.0155 0.1043-0.0193 0.3091-0.0966 0.3091-0.2164 0-0.0232-0.0078-0.0502-0.0309-0.0811l-2.1676-2.8901 2.1444-1.9667c0.0309-0.027 0.0463-0.058 0.0463-0.0811 0-0.0773-0.1081-0.1391-0.1893-0.1585-0.0463-0.0115-0.1043-0.0193-0.1661-0.0193-0.1121 0-0.2319 0.0271-0.2975 0.0889l-2.9752 2.724v-2.6158c0-0.1507-0.2781-0.1971-0.3941-0.1971-0.112 0-0.3902 0.0464-0.3902 0.1971v5.0345c0 0.1507 0.2782 0.1971 0.3902 0.1971 0.116 0 0.3941-0.0464 0.3941-0.1971v-1.6923l0.8501-0.7767 1.8855 2.5154c0.0811 0.1082 0.2705 0.1507 0.4328 0.1507zm13.862-0.0928c0.127 0 0.394-0.0541 0.394-0.2164 0-0.1622-0.267-0.2163-0.394-0.2163h-3.107v-1.9744h2.616c0.124 0 0.39-0.0541 0.39-0.2164s-0.266-0.2164-0.39-0.2164h-2.616v-1.9705h3.107c0.127 0 0.394-0.0541 0.394-0.2164s-0.267-0.2163-0.394-0.2163h-3.47c-0.123 0-0.421 0.0695-0.421 0.2318v4.7795c0 0.1623 0.294 0.2318 0.421 0.2318zm12.15 0.0464c0.124 0 0.391-0.0541 0.391-0.2164v-4.6868h1.565c0.123 0 0.39-0.0541 0.39-0.2163 0-0.1623-0.267-0.2164-0.39-0.2164h-3.914c-0.124 0-0.391 0.0541-0.391 0.2164 0 0.1622 0.267 0.2163 0.391 0.2163h1.564v4.6868c0 0.1623 0.267 0.2164 0.394 0.2164z'], ['fill' => '#000'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></svg><?php } elseif (method_exists($_pug_temp = (isset($mode) ? $mode : null) == 'rev', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'logo'], ['width' => '37.335'], ['height' => '37.048'], ['fill' => 'none'], ['version' => '1.1'], ['viewBox' => '0 0 37.335 37.048'], ['xmlns' => 'http://www.w3.org/2000/svg'], ['xmlns:cc' => 'http://creativecommons.org/ns#'], ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'], ['xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>><metadata><rdf:rdf><cc:work<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:about' => ''])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><dc:format>image/svg+xml</dc:format><dc:type<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:resource' => 'http://purl.org/dc/dcmitype/StillImage'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></dc:type><dc:title></dc:title></cc:work></rdf:rdf></metadata><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['clip-rule' => 'evenodd'], ['fill-rule' => 'evenodd'], ['id' => 'logo-pattern'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#b87a00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c-2.197 3.009-6.0505 4.5835-9.9102 3.6924-3.8791-0.8956-6.6608-4.0268-7.2983-7.7185 0 0-1.5041-0.8915-2.3645-1.601-0.63752-0.5257-1.5345-1.4567-1.5345-1.4567-0.72173 6.6658 3.6385 12.969 10.334 14.515 6.6944 1.5455 13.375-2.2067 15.65-8.512 0 0-1.215 0.4355-2.0164 0.6328-1.0978 0.2702-2.8601 0.448-2.8601 0.448z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm15.591 4.086c0.4307 0.09945 0.8479 0.22646 1.2502 0.37874 0 0 0.9664-0.59505 1.6259-0.89616 1.105-0.50449 2.97-0.9241 2.97-0.9241-1.4412-1.0704-3.1219-1.8676-4.983-2.2973-7.2271-1.6685-14.439 2.8376-16.107 10.065-1.0002 4.3323 0.21845 8.659 2.912 11.789 0 0 0.16213-1.4581 0.4234-2.3555 0.28755-0.9877 1.0244-2.4268 1.0244-2.4268-0.85006-1.8574-1.116-3.9997-0.62094-6.144 1.1918-5.1622 6.3428-8.3809 11.505-7.1891z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm33.249 18.008c-0.7059 3.0577-2.8008 5.4334-5.4643 6.6148l0.2556 4.0061c4.3363-1.4089 7.8472-4.9916 8.9475-9.7577 1.6686-7.2272-2.8376-14.439-10.065-16.107-2.3067-0.53256-4.6119-0.43607-6.7357 0.17716l2.4253 3.4002c1.1183-0.15182 2.2825-0.10744 3.4472 0.16146 5.1623 1.1918 8.381 6.3428 7.1892 11.505z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#e6a503'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c0.7414-1.0154 1.2942-2.1942 1.5949-3.4968 0.7059-3.0576-0.1355-6.1113-2.0116-8.3406l1.9859-3.4887c3.2798 3.1672 4.8649 7.9264 3.7645 12.692-0.1167 0.5057-0.2606 0.9981-0.4298 1.476 0 0-1.4754 0.7737-2.504 0.9972-0.9179 0.1994-2.3999 0.1604-2.3999 0.1604zm-12.318-18.286c-4.1383 1.5003-7.4559 5.0023-8.5198 9.6106-0.1227 0.5315-0.21202 1.0629-0.2693 1.5921 0 0 1.0053 1.2847 1.8221 1.9169 0.74283 0.575 2.0867 1.1963 2.0867 1.1963-0.2213-1.2377-0.2014-2.5395 0.0994-3.8421 0.7059-3.0576 2.8007-5.4334 5.4642-6.6147z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm11.275 22.78c1.5374 0.3549 3.0738 0.3187 4.505-0.0436l3.0889 2.9782c-2.5667 1.1384-5.5106 1.4845-8.4571 0.8042-2.9002-0.6695-5.3623-2.2317-7.1629-4.3293 0 0 0.17885-1.6924 0.51981-2.7177 0.27813-0.8364 0.93808-2.0529 0.93808-2.0529 1.1965 2.6143 3.5502 4.6643 6.5683 5.3611zm5.5291-18.329c3.9644 1.4822 6.4861 5.4149 6.2022 9.6172l3.3189 3.1291c0.0711-0.2442 0.1357-0.4919 0.1937-0.7429 1.2388-5.3661-0.9263-10.723-5.0819-13.81 0 0-1.5708 0.26125-2.5098 0.63653-0.879 0.35133-2.1231 1.1698-2.1231 1.1698z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm21.744 25.197c3.0276 0.699 6.0512-0.1191 8.2747-1.9565 0 0 0.0465 1.394-0.0778 2.2736-0.152 1.0748-0.7424 2.6784-0.7424 2.6784-2.5336 1.0897-5.4244 1.4113-8.3177 0.7433-3.1162-0.7194-5.7265-2.4693-7.5541-4.8067l4.5242-0.8327c1.1004 0.8956 2.4174 1.5599 3.8931 1.9006zm-6.8636-12.611c1.48-4.1062 5.5756-6.6975 9.9104-6.2886 0 0-0.7462-1.2806-1.3767-1.9804-0.6768-0.75122-1.9768-1.6724-1.9768-1.6724-5.0879 0.9523-9.3824 4.8184-10.621 10.184z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#fad958'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm18.754 14.268c1.8704 0.4318 3.4856 1.3833 4.7284 2.6668l2.2902-3.1289c-1.6705-1.5657-3.764-2.7247-6.1554-3.2768-2.904-0.6704-5.8054-0.3439-8.3457 0.7554 0 0-0.4695 1.3778-0.628 2.2924-0.1773 1.0231-0.1709 2.6532-0.1709 2.6532 2.224-1.8414 5.2508-2.6618 8.2814-1.9621z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm22.78 15.591c-0.8511 3.6865-3.7212 6.3819-7.1727 7.1876l1.5402 3.5633c4.5302-1.3081 8.2354-4.9673 9.3714-9.8877 0.1231-0.5331 0.2125-1.0662 0.2698-1.597 0 0-0.6535-0.8835-1.1611-1.3654-0.9216-0.8751-2.7482-1.7428-2.7482-1.7428 0.2213 1.2377 0.2013 2.5395-0.0994 3.842z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm20.531 24.832c-4.4411-1.6602-7.0718-6.3955-5.9763-11.14l-3.7389-0.8631c-1.2388 5.366 0.9263 10.723 5.0819 13.81 0 0 1.5708-0.2613 2.5098-0.6365 0.8792-0.3513 2.1235-1.1697 2.1235-1.1697z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g></g></svg><?php } elseif (method_exists($_pug_temp = (isset($mode) ? $mode : null) == 'rev-full', "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><svg<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'logo'], ['width' => '122.63'], ['height' => '37.048'], ['fill' => 'none'], ['version' => '1.1'], ['viewBox' => '0 0 122.63 37.048'], ['xmlns' => 'http://www.w3.org/2000/svg'], ['xmlns:cc' => 'http://creativecommons.org/ns#'], ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'], ['xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>><metadata><rdf:rdf><cc:work<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:about' => ''])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><dc:format>image/svg+xml</dc:format><dc:type<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rdf:resource' => 'http://purl.org/dc/dcmitype/StillImage'])
) ? var_export($_pug_temp, true) : $_pug_temp) ?>></dc:type><dc:title></dc:title></cc:work></rdf:rdf></metadata><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['clip-rule' => 'evenodd'], ['fill-rule' => 'evenodd'], ['id' => 'logo-pattern'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#b87a00'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c-2.197 3.009-6.0505 4.5835-9.9102 3.6924-3.8791-0.8956-6.6608-4.0268-7.2983-7.7185 0 0-1.5041-0.8915-2.3645-1.601-0.63752-0.5257-1.5345-1.4567-1.5345-1.4567-0.72173 6.6658 3.6385 12.969 10.334 14.515 6.6944 1.5455 13.375-2.2067 15.65-8.512 0 0-1.215 0.4355-2.0164 0.6328-1.0978 0.2702-2.8601 0.448-2.8601 0.448z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm15.591 4.086c0.4307 0.09945 0.8479 0.22646 1.2502 0.37874 0 0 0.9664-0.59505 1.6259-0.89616 1.105-0.50449 2.97-0.9241 2.97-0.9241-1.4412-1.0704-3.1219-1.8676-4.983-2.2973-7.2271-1.6685-14.439 2.8376-16.107 10.065-1.0002 4.3323 0.21845 8.659 2.912 11.789 0 0 0.16213-1.4581 0.4234-2.3555 0.28755-0.9877 1.0244-2.4268 1.0244-2.4268-0.85006-1.8574-1.116-3.9997-0.62094-6.144 1.1918-5.1622 6.3428-8.3809 11.505-7.1891z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm33.249 18.008c-0.7059 3.0577-2.8008 5.4334-5.4643 6.6148l0.2556 4.0061c4.3363-1.4089 7.8472-4.9916 8.9475-9.7577 1.6686-7.2272-2.8376-14.439-10.065-16.107-2.3067-0.53256-4.6119-0.43607-6.7357 0.17716l2.4253 3.4002c1.1183-0.15182 2.2825-0.10744 3.4472 0.16146 5.1623 1.1918 8.381 6.3428 7.1892 11.505z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#e6a503'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm24.348 29.27c0.7414-1.0154 1.2942-2.1942 1.5949-3.4968 0.7059-3.0576-0.1355-6.1113-2.0116-8.3406l1.9859-3.4887c3.2798 3.1672 4.8649 7.9264 3.7645 12.692-0.1167 0.5057-0.2606 0.9981-0.4298 1.476 0 0-1.4754 0.7737-2.504 0.9972-0.9179 0.1994-2.3999 0.1604-2.3999 0.1604zm-12.318-18.286c-4.1383 1.5003-7.4559 5.0023-8.5198 9.6106-0.1227 0.5315-0.21202 1.0629-0.2693 1.5921 0 0 1.0053 1.2847 1.8221 1.9169 0.74283 0.575 2.0867 1.1963 2.0867 1.1963-0.2213-1.2377-0.2014-2.5395 0.0994-3.8421 0.7059-3.0576 2.8007-5.4334 5.4642-6.6147z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm11.275 22.78c1.5374 0.3549 3.0738 0.3187 4.505-0.0436l3.0889 2.9782c-2.5667 1.1384-5.5106 1.4845-8.4571 0.8042-2.9002-0.6695-5.3623-2.2317-7.1629-4.3293 0 0 0.17885-1.6924 0.51981-2.7177 0.27813-0.8364 0.93808-2.0529 0.93808-2.0529 1.1965 2.6143 3.5502 4.6643 6.5683 5.3611zm5.5291-18.329c3.9644 1.4822 6.4861 5.4149 6.2022 9.6172l3.3189 3.1291c0.0711-0.2442 0.1357-0.4919 0.1937-0.7429 1.2388-5.3661-0.9263-10.723-5.0819-13.81 0 0-1.5708 0.26125-2.5098 0.63653-0.879 0.35133-2.1231 1.1698-2.1231 1.1698z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm21.744 25.197c3.0276 0.699 6.0512-0.1191 8.2747-1.9565 0 0 0.0465 1.394-0.0778 2.2736-0.152 1.0748-0.7424 2.6784-0.7424 2.6784-2.5336 1.0897-5.4244 1.4113-8.3177 0.7433-3.1162-0.7194-5.7265-2.4693-7.5541-4.8067l4.5242-0.8327c1.1004 0.8956 2.4174 1.5599 3.8931 1.9006zm-6.8636-12.611c1.48-4.1062 5.5756-6.6975 9.9104-6.2886 0 0-0.7462-1.2806-1.3767-1.9804-0.6768-0.75122-1.9768-1.6724-1.9768-1.6724-5.0879 0.9523-9.3824 4.8184-10.621 10.184z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g><g<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['fill' => '#fad958'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm18.754 14.268c1.8704 0.4318 3.4856 1.3833 4.7284 2.6668l2.2902-3.1289c-1.6705-1.5657-3.764-2.7247-6.1554-3.2768-2.904-0.6704-5.8054-0.3439-8.3457 0.7554 0 0-0.4695 1.3778-0.628 2.2924-0.1773 1.0231-0.1709 2.6532-0.1709 2.6532 2.224-1.8414 5.2508-2.6618 8.2814-1.9621z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm22.78 15.591c-0.8511 3.6865-3.7212 6.3819-7.1727 7.1876l1.5402 3.5633c4.5302-1.3081 8.2354-4.9673 9.3714-9.8877 0.1231-0.5331 0.2125-1.0662 0.2698-1.597 0 0-0.6535-0.8835-1.1611-1.3654-0.9216-0.8751-2.7482-1.7428-2.7482-1.7428 0.2213 1.2377 0.2013 2.5395-0.0994 3.842z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm20.531 24.832c-4.4411-1.6602-7.0718-6.3955-5.9763-11.14l-3.7389-0.8631c-1.2388 5.366 0.9263 10.723 5.0819 13.81 0 0 1.5708-0.2613 2.5098-0.6365 0.8792-0.3513 2.1235-1.1697 2.1235-1.1697z'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></g></g><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm56.376 17.173h-6.3421l-1.0681 2.1527h-3.4277l5.953-11.376h3.4277l5.953 11.376h-3.4277zm-5.241-2.2189h4.1564l-2.0699-4.1895zm25.773 0.1656c0 0.7562-0.1242 1.4075-0.3726 1.954-0.2428 0.5464-0.6016 0.9963-1.0763 1.3495-0.4692 0.3533-1.046 0.6155-1.7304 0.7866-0.6845 0.1656-1.4655 0.2484-2.3431 0.2484-0.414 0-0.839-0.0221-1.2751-0.0663-0.436-0.0386-0.8555-0.0883-1.2585-0.149-0.4029-0.0607-0.7755-0.1242-1.1177-0.1904-0.3367-0.0718-0.6182-0.138-0.8445-0.1987v-2.6661c0.2318 0.0939 0.4857 0.1905 0.7617 0.2898 0.276 0.0939 0.5823 0.1794 0.919 0.2567 0.3422 0.0773 0.7176 0.1408 1.126 0.1904 0.414 0.0497 0.8694 0.0745 1.3662 0.0745 0.5354 0 0.9797-0.0579 1.333-0.1738 0.3587-0.1215 0.6458-0.287 0.861-0.4968 0.2153-0.2153 0.3671-0.4664 0.4554-0.7534 0.0883-0.2871 0.1325-0.6017 0.1325-0.9439v-6.6816h3.0634zm22.85 0.3726c0 0.5685-0.141 1.1039-0.423 1.6062-0.281 0.5023-0.703 0.9411-1.266 1.3164-0.563 0.3754-1.267 0.6735-2.112 0.8942-0.8387 0.2153-1.8212 0.3229-2.9472 0.3229s-2.114-0.1076-2.9641-0.3229c-0.8445-0.2207-1.5483-0.5188-2.1113-0.8942-0.563-0.3753-0.9852-0.8141-1.2667-1.3164s-0.4223-1.0377-0.4223-1.6062v-7.5427h3.0634v6.4415c0 0.3808 0.047 0.7424 0.1408 1.0846 0.0993 0.3367 0.2815 0.632 0.5464 0.8859 0.2705 0.2539 0.6458 0.4554 1.1261 0.6044 0.4857 0.1491 1.1149 0.2236 1.8877 0.2236 0.7672 0 1.391-0.0745 1.8712-0.2236 0.48-0.149 0.853-0.3505 1.118-0.6044 0.27-0.2539 0.452-0.5492 0.546-0.8859 0.094-0.3422 0.141-0.7038 0.141-1.0846v-6.4415h3.072zm12.484 3.8334h-3.072v-11.376h7.7c0.828 0 1.529 0.0856 2.103 0.2567 0.579 0.1711 1.049 0.4139 1.407 0.7286 0.365 0.3146 0.627 0.6954 0.787 1.1425 0.165 0.4416 0.248 0.9356 0.248 1.4821 0 0.4802-0.069 0.8997-0.207 1.2585-0.132 0.3588-0.314 0.6679-0.546 0.9273-0.226 0.2539-0.491 0.4692-0.795 0.6458s-0.624 0.3229-0.96 0.4388l3.725 4.4958h-3.593l-3.444-4.1895h-3.353zm6.068-7.7828c0-0.2208-0.03-0.4084-0.091-0.563-0.055-0.1545-0.154-0.2787-0.298-0.3726-0.143-0.0993-0.336-0.1711-0.579-0.2152-0.238-0.0442-0.536-0.0663-0.894-0.0663h-4.206v2.4342h4.206c0.358 0 0.656-0.0221 0.894-0.0662 0.243-0.0442 0.436-0.1132 0.579-0.207 0.144-0.0994 0.243-0.2263 0.298-0.3809 0.061-0.1545 0.091-0.3422 0.091-0.563z'], ['fill' => '#fff'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path><path<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['d' => 'm51.212 28.375c0.1198 0 0.3902-0.0541 0.3902-0.2126v-4.9997c0-0.17-0.2627-0.2164-0.3941-0.2164-0.1159 0-0.3554 0.0425-0.4057 0.1623l-1.7696 4.3275-1.9357-4.3275c-0.0619-0.1352-0.2744-0.1623-0.4096-0.1623-0.1236 0-0.4521 0.0541-0.4521 0.2164v5.0616c0 0.1236 0.2164 0.1507 0.3053 0.1507 0.0888 0 0.3052-0.0271 0.3052-0.1507v-3.7131l1.6614 3.7015c0.058 0.1275 0.3014 0.1623 0.4251 0.1623 0.1236 0 0.3632-0.031 0.4173-0.1662l1.4682-3.6049v3.5585c0 0.1585 0.2705 0.2126 0.3941 0.2126zm14.751 0c0.1236 0 0.4134-0.0503 0.4134-0.2087 0-0.0154-0.0039-0.0309-0.0116-0.0502l-2.2101-5.0075c-0.0579-0.1275-0.2704-0.1623-0.4018-0.1623-0.1237 0-0.3864 0.0309-0.4405 0.1584l-2.1946 5.0809c-0.0039 0.0116-0.0078 0.0232-0.0078 0.0348 0 0.0966 0.1585 0.1546 0.313 0.1546 0.1275 0 0.255-0.0387 0.2898-0.1198l0.7109-1.6499h2.4072l0.7071 1.6035c0.054 0.1237 0.2704 0.1662 0.425 0.1662zm-1.3253-2.2024h-2.0246l1.0007-2.3221zm15.62 2.2024c0.1043 0 0.2125-0.0232 0.2898-0.0735 0.0695-0.0425 0.1005-0.0927 0.1005-0.1429 0-0.0618-0.0464-0.1198-0.1275-0.1623-0.4251-0.2125-0.6492-0.7303-0.8308-1.136-0.1584-0.3477-0.3284-0.7263-0.6182-0.9929 0.6917-0.1353 1.2983-0.6608 1.2983-1.3949 0-0.8925-0.8655-1.4334-1.704-1.4334h-2.3917c-0.1236 0-0.3902 0.054-0.3902 0.2163v4.9032c0 0.1623 0.2666 0.2164 0.3902 0.2164 0.1276 0 0.3942-0.0541 0.3942-0.2164v-2.2487h1.3098c0.5216 0 0.7766 0.6645 0.9505 1.0548 0.2357 0.5216 0.4791 1.0586 1.0664 1.3523 0.0734 0.0386 0.17 0.058 0.2627 0.058zm-1.5919-2.8979h-1.9975v-2.0053h1.9975c0.5216 0 0.9196 0.5139 0.9196 1.0007 0 0.4869-0.398 1.0046-0.9196 1.0046zm15.535 2.8979c0.0579 0 0.112-0.0078 0.1584-0.0155 0.1043-0.0193 0.3091-0.0966 0.3091-0.2164 0-0.0232-0.0078-0.0502-0.0309-0.0811l-2.1676-2.8901 2.1444-1.9667c0.0309-0.027 0.0463-0.058 0.0463-0.0811 0-0.0773-0.1081-0.1391-0.1893-0.1585-0.0463-0.0115-0.1043-0.0193-0.1661-0.0193-0.1121 0-0.2319 0.0271-0.2975 0.0889l-2.9752 2.724v-2.6158c0-0.1507-0.2781-0.1971-0.3941-0.1971-0.112 0-0.3902 0.0464-0.3902 0.1971v5.0345c0 0.1507 0.2782 0.1971 0.3902 0.1971 0.116 0 0.3941-0.0464 0.3941-0.1971v-1.6923l0.8501-0.7767 1.8855 2.5154c0.0811 0.1082 0.2705 0.1507 0.4328 0.1507zm13.862-0.0928c0.127 0 0.394-0.0541 0.394-0.2164 0-0.1622-0.267-0.2163-0.394-0.2163h-3.107v-1.9744h2.616c0.124 0 0.39-0.0541 0.39-0.2164s-0.266-0.2164-0.39-0.2164h-2.616v-1.9705h3.107c0.127 0 0.394-0.0541 0.394-0.2164s-0.267-0.2163-0.394-0.2163h-3.47c-0.123 0-0.421 0.0695-0.421 0.2318v4.7795c0 0.1623 0.294 0.2318 0.421 0.2318zm12.15 0.0464c0.124 0 0.391-0.0541 0.391-0.2164v-4.6868h1.565c0.123 0 0.39-0.0541 0.39-0.2163 0-0.1623-0.267-0.2164-0.39-0.2164h-3.914c-0.124 0-0.391 0.0541-0.391 0.2164 0 0.1622 0.267 0.2163 0.391 0.2163h1.564v4.6868c0 0.1623 0.267 0.2164 0.394 0.2164z'], ['fill' => '#fff'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></path></svg><?php } ?><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['button'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'mode', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    ?><button<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'button'], ['class' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('class', 'button_mode_' . (isset($mode) ? $mode : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><?= htmlspecialchars((is_bool($_pug_temp = (isset($text) ? $text : null)) ? var_export($_pug_temp, true) : $_pug_temp)) ?><?php if (method_exists($_pug_temp = (isset($block) ? $block : null), "__toBoolean")
        ? $_pug_temp->__toBoolean()
        : $_pug_temp) { ?><?= (is_bool($_pug_temp = $__pug_children(get_defined_vars())) ? var_export($_pug_temp, true) : $_pug_temp) ?><?php } ?></button><?php
}; ?><?php if (!isset($__pug_mixins)) {
    $__pug_mixins = [];
}
$__pug_mixins['input'] = function ($block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children) use (&$__pug_mixins, &$pugModule) {
    $__pug_values = [];
    foreach ($__pug_arguments as $__pug_argument) {
        if ($__pug_argument[0]) {
            foreach ($__pug_argument[1] as $__pug_value) {
                $__pug_values[] = $__pug_value;
            }
            continue;
        }
        $__pug_values[] = $__pug_argument[1];
    }
    $__pug_attributes = [[false, 'placeholder', null]];
    $__pug_names = [];
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        ${$__pug_name} = null;
    }
    foreach ($__pug_attributes as $__pug_argument) {
        $__pug_name = ltrim($__pug_argument[1], "$");
        $__pug_names[] = $__pug_name;
        if ($__pug_argument[0]) {
            ${$__pug_name} = $__pug_values;
            break;
        }
        ${$__pug_name} = array_shift($__pug_values);
        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {
            ${$__pug_name} = $__pug_argument[2];
        }
    }
    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {
        if (!in_array($__pug_key, $__pug_names)) {
            $$__pug_key = &$__pug_value;
        }
    }
    ?><input<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment']($attributes, ['class' => 'input'], ['placeholder' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('placeholder', (isset($placeholder) ? $placeholder : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><?php
}; ?><!DOCTYPE html><html<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['lang' => 'en'])) ? var_export($_pug_temp, true) : $_pug_temp) ?>><head><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['charset' => 'UTF-8'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['name' => 'viewport'], ['content' => 'width=device-width, initial-scale=1.0'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><meta<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['http-equiv' => 'X-UA-Compatible'], ['content' => 'ie=edge'])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /><title>Title</title><link<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['rel' => 'stylesheet'], ['href' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('href', '' . (isset($link) ? $link : null) . '.css?ver=' . (isset($ver) ? $ver : null))])) ? var_export($_pug_temp, true) : $_pug_temp) ?> /></head><body>Hello, World!</body><script<?= (is_bool($_pug_temp = $pugModule['Phug\\Formatter\\Format\\BasicFormat::attributes_assignment'](array(  ), ['src' => $pugModule['Phug\\Formatter\\Format\\BasicFormat::array_escape']('src', '' . (isset($link) ? $link : null) . '.bundle.js')])) ? var_export($_pug_temp, true) : $_pug_temp) ?>></script></html>