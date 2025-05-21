<?php

/*
 TWIX: Optimized TWIG Engine
 (c) MaxD
*/
define('TWIX_VER', '1.23');
define('TWIX_MONITOR_CHANGES', true);

function twix_render($template, $data, $code = '') {

    $cls = str_replace('/', '__', str_replace('.twig', '', $template));
    $cls = str_replace('-', '_', $cls);
    $file = DIR_CACHE.'twix/'.$cls.'.php';

    if (!defined('DIR_MODIFICATION')) $dir_mod_template = 'x';
    else
    if (defined('DIR_CATALOG'))
      $dir_mod_template = DIR_MODIFICATION . 'admin/view/template/';
    else $dir_mod_template = DIR_MODIFICATION . 'catalog/view/theme/';

    if (!$code) {
        if (VERSION > 4) $twig = DIR_OPENCART . $template;
        else $twig = DIR_TEMPLATE . $template;
        $org = $twig;
        if (is_file($dir_mod_template . $template)) $twig = $dir_mod_template . $template;

        if (class_exists('VQMod')) {
            $twig = \VQMod::modCheck($twig, $org);
        }
    } else $twig = "Event code replacing ".DIR_TEMPLATE . $template.", CRC ". crc32($code);

    try {
        if (is_file($file)) {

            if (twix_cache_made_of($file, $twig)) {

                if (!class_exists($cls)) {

                    if (!TWIX_MONITOR_CHANGES && !filesize($file))
                        throw new \Exception('Fallback to TWIG');

                    require_once $file;
                }
                    $template = new $cls(null);
                    return $template->render($data);
            }
            @unlink($file);
        }

        require_once "twix_compile.php";

        $loader = new \Twix_Loader_Filesystem(false, $code);

        if (is_dir($dir_mod_template))
            $loader->addPath($dir_mod_template);

        if (VERSION > 4) $loader->addPath(DIR_OPENCART);
        else $loader->addPath(DIR_TEMPLATE);

        $config = array('autoescape' => false);
        $config['cache'] = DIR_CACHE;
        $twix = new \Twix_Environment($loader, $config);
        $template = $twix->loadTemplate($template);
        return $template->render($data);

    } catch (\Exception $e) {} catch (\Error $e) {}

    if (TWIX_MONITOR_CHANGES)
        file_put_contents($file, "/* Fallback to TWIG! \n Twix ver. ".TWIX_VER."  Source: $twig */");
    else file_put_contents($file, '');

    throw new \Exception('Fallback to TWIG');
}

function twix_cache_made_of($cache, $twig) {

    if (!TWIX_MONITOR_CHANGES) return true;
    if (is_file($twig) && filemtime($twig) > @filemtime($cache)) return false;

    $head = @file_get_contents($cache, false, null, 0, 512);
    $ver = twix_inside($head, 'Twix ver.', 'Source:');
    $file = twix_inside($head, 'Source:', '*/');
    if ($file != $twig || $ver != TWIX_VER) return false;

    if (strpos($head, 'Fallback to TWIG')) throw new \Exception('Fallback to TWIG');
    return true;
}

// Find the text inside the $start and $end.
//    If $start="" then start is start of content.
//    If $end="" then end is end of content.
function twix_inside($content, $start, $end = "") {
    $r = '';
    if ($start) $s = stripos($content, $start);
    else $s=0;
    if ($s !== false) {
        $s += strlen($start);
        if ($end) $e = stripos($content, $end, $s);
        else $e = strlen($content);
        if ($e !== false)
            $r = trim(substr($content, $s, $e - $s));
    }

    return $r;
}

// Find inside, if not found - return original string

function twix_if_inside ($content, $start, $end="") {
    $res = twix_inside ($content, $start, $end);
    if ($res) return $res;
    return $content;
}

function twix_include($env, $context, $template, $variables = array()) {
    return twix_render($template, array_merge($context, $variables));
}

interface Twix_TemplateInterface
{
    const ANY_CALL = 'any';
    const ARRAY_CALL = 'array';
    const METHOD_CALL = 'method';

    public function render(array $context);

    public function display(array $context, array $blocks = array());

    public function getEnvironment();
}

abstract class Twix_Template implements Twix_TemplateInterface
{
    protected static $cache = array();
    protected $parent;
    protected $parents = array();
    protected $env;
    protected $blocks = array();
    protected $traits = array();

    public function __construct($env)
    {
        $this->env = $env;
    }

    abstract public function getTemplateName();

    public function getEnvironment()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.20 and will be removed in 2.0.', E_USER_DEPRECATED);
        return $this->env;
    }

    public function getParent(array $context)
    {
        if (null !== $this->parent) {
            return $this->parent;
        }
        try {
            $parent = $this->doGetParent($context);
            if (false === $parent) {
                return false;
            }
            if ($parent instanceof self) {
                return $this->parents[$parent->getTemplateName()] = $parent;
            }
            if (!isset($this->parents[$parent])) {
                $this->parents[$parent] = $this->loadTemplate($parent);
            }
        } catch (Twix_Error_Loader $e) {
            $e->setTemplateFile(null);
            $e->guess();
            throw $e;
        }
        return $this->parents[$parent];
    }
    protected function doGetParent(array $context)
    {
        return false;
    }
    public function isTraitable()
    {
        return true;
    }

    public function displayParentBlock($name, array $context, array $blocks = array())
    {
        $name = (string) $name;
        if (isset($this->traits[$name])) {
            $this->traits[$name][0]->displayBlock($name, $context, $blocks, false);
        } elseif (false !== $parent = $this->getParent($context)) {
            $parent->displayBlock($name, $context, $blocks, false);
        } else {
            throw new Twix_Error_Runtime(sprintf('The template has no parent and no traits defining the "%s" block', $name), -1, $this->getTemplateName());
        }
    }

    public function displayBlock($name, array $context, array $blocks = array(), $useBlocks = true)
    {
        $name = (string) $name;
        if ($useBlocks && isset($blocks[$name])) {
            $template = $blocks[$name][0];
            $block = $blocks[$name][1];
        } elseif (isset($this->blocks[$name])) {
            $template = $this->blocks[$name][0];
            $block = $this->blocks[$name][1];
        } else {
            $template = null;
            $block = null;
        }
        if (null !== $template) {
            // avoid RCEs when sandbox is enabled
            if (!$template instanceof self) {
                throw new LogicException('A block must be a method on a Twix_Template instance.');
            }
            try {
                $template->$block($context, $blocks);
            } catch (Twix_Error $e) {
                if (!$e->getTemplateFile()) {
                    $e->setTemplateFile($template->getTemplateName());
                }
                // this is mostly useful for Twix_Error_Loader exceptions
                // see Twix_Error_Loader
                if (false === $e->getTemplateLine()) {
                    $e->setTemplateLine(-1);
                    $e->guess();
                }
                throw $e;
            } catch (Exception $e) {
                throw new Twix_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $template->getTemplateName(), $e);
            }
        } elseif (false !== $parent = $this->getParent($context)) {
            $parent->displayBlock($name, $context, array_merge($this->blocks, $blocks), false);
        }
    }

    public function renderParentBlock($name, array $context, array $blocks = array())
    {
        ob_start();
        $this->displayParentBlock($name, $context, $blocks);
        return ob_get_clean();
    }

    public function renderBlock($name, array $context, array $blocks = array(), $useBlocks = true)
    {
        ob_start();
        $this->displayBlock($name, $context, $blocks, $useBlocks);
        return ob_get_clean();
    }

    public function hasBlock($name)
    {
        return isset($this->blocks[(string) $name]);
    }

    public function getBlockNames()
    {
        return array_keys($this->blocks);
    }
    protected function loadTemplate($template, $templateName = null, $line = null, $index = null)
    {
        try {
            if (is_array($template)) {
                return $this->env->resolveTemplate($template);
            }
            if ($template instanceof self) {
                return $template;
            }
            return $this->env->loadTemplate($template, $index);
        } catch (Twix_Error $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($templateName ? $templateName : $this->getTemplateName());
            }
            if ($e->getTemplateLine()) {
                throw $e;
            }
            if (!$line) {
                $e->guess();
            } else {
                $e->setTemplateLine($line);
            }
            throw $e;
        }
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getSource()
    {
        $reflector = new ReflectionClass($this);
        $file = $reflector->getFileName();
        if (!is_file($file)) {
            return;
        }
        $source = file($file, FILE_IGNORE_NEW_LINES);
        array_splice($source, 0, $reflector->getEndLine());
        $i = 0;
        while (isset($source[$i]) && '/* */' === substr_replace($source[$i], '', 3, -2)) {
            $source[$i] = str_replace('*//* ', '*/', substr($source[$i], 3, -2));
            ++$i;
        }
        array_splice($source, $i);
        return implode("\n", $source);
    }

    public function display(array $context, array $blocks = array())
    {
        $this->displayWithErrorHandling($context, array_merge($this->blocks, $blocks)); //MaxD2
    }

    public function render(array $context)
    {
        $level = ob_get_level();
        ob_start();
        try {
            $this->display($context);
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean();
    }
    protected function displayWithErrorHandling(array $context, array $blocks = array())
    {
        try {
            $this->doDisplay($context, $blocks);
        } catch (Twix_Error $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($this->getTemplateName());
            }
            // this is mostly useful for Twix_Error_Loader exceptions
            // see Twix_Error_Loader
            if (false === $e->getTemplateLine()) {
                $e->setTemplateLine(-1);
                $e->guess();
            }
            throw $e;
        } catch (Exception $e) {
            throw new Twix_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $this->getTemplateName(), $e);
        }
    }

    abstract protected function doDisplay(array $context, array $blocks = array());

    final protected function getContext($context, $item, $ignoreStrictCheck = false)
    {
        if (!array_key_exists($item, $context)) {
            if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
                return;
            }
            throw new Twix_Error_Runtime(sprintf('Variable "%s" does not exist', $item), -1, $this->getTemplateName());
        }
        return $context[$item];
    }

    protected function getAttribute($object, $item, array $arguments = array(), $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        // array
        if (self::METHOD_CALL !== $type) {
            $arrayItem = is_bool($item) || is_float($item) ? (int) $item : $item;
            if ((is_array($object) && array_key_exists($arrayItem, $object))
                || ($object instanceof ArrayAccess && isset($object[$arrayItem]))
            ) {
                if ($isDefinedTest) {
                    return true;
                }
                return $object[$arrayItem];
            }
            if (self::ARRAY_CALL === $type || !is_object($object)) {
                if ($isDefinedTest) {
                    return false;
                }
                if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
                    return;
                }
                if ($object instanceof ArrayAccess) {
                    $message = sprintf('Key "%s" in object with ArrayAccess of class "%s" does not exist', $arrayItem, get_class($object));
                } elseif (is_object($object)) {
                    $message = sprintf('Impossible to access a key "%s" on an object of class "%s" that does not implement ArrayAccess interface', $item, get_class($object));
                } elseif (is_array($object)) {
                    if (empty($object)) {
                        $message = sprintf('Key "%s" does not exist as the array is empty', $arrayItem);
                    } else {
                        $message = sprintf('Key "%s" for array with keys "%s" does not exist', $arrayItem, implode(', ', array_keys($object)));
                    }
                } elseif (self::ARRAY_CALL === $type) {
                    if (null === $object) {
                        $message = sprintf('Impossible to access a key ("%s") on a null variable', $item);
                    } else {
                        $message = sprintf('Impossible to access a key ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
                    }
                } elseif (null === $object) {
                    $message = sprintf('Impossible to access an attribute ("%s") on a null variable', $item);
                } else {
                    $message = sprintf('Impossible to access an attribute ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
                }
                throw new Twix_Error_Runtime($message, -1, $this->getTemplateName());
            }
        }
        if (!is_object($object)) {
            if ($isDefinedTest) {
                return false;
            }
            if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
                return;
            }
            if (null === $object) {
                $message = sprintf('Impossible to invoke a method ("%s") on a null variable', $item);
            } else {
                $message = sprintf('Impossible to invoke a method ("%s") on a %s variable ("%s")', $item, gettype($object), $object);
            }
            throw new Twix_Error_Runtime($message, -1, $this->getTemplateName());
        }
        // object property
        if (self::METHOD_CALL !== $type && !$object instanceof self) { // Twix_Template does not have public properties, and we don't want to allow access to internal ones
            if (isset($object->$item) || array_key_exists((string) $item, $object)) {
                if ($isDefinedTest) {
                    return true;
                }
                if ($this->env->hasExtension('sandbox')) {
                    $this->env->getExtension('sandbox')->checkPropertyAllowed($object, $item);
                }
                return $object->$item;
            }
        }
        $class = get_class($object);
        // object method
        if (!isset(self::$cache[$class]['methods'])) {
            // get_class_methods returns all methods accessible in the scope, but we only want public ones to be accessible in templates
            if ($object instanceof self) {
                $ref = new ReflectionClass($class);
                $methods = array();
                foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
                    $methodName = strtolower($refMethod->name);
                    // Accessing the environment from templates is forbidden to prevent untrusted changes to the environment
                    if ('getenvironment' !== $methodName) {
                        $methods[$methodName] = true;
                    }
                }
                self::$cache[$class]['methods'] = $methods;
            } else {
                self::$cache[$class]['methods'] = array_change_key_case(array_flip(get_class_methods($object)));
            }
        }
        $call = false;
        $lcItem = strtolower($item);
        if (isset(self::$cache[$class]['methods'][$lcItem])) {
            $method = (string) $item;
        } elseif (isset(self::$cache[$class]['methods']['get'.$lcItem])) {
            $method = 'get'.$item;
        } elseif (isset(self::$cache[$class]['methods']['is'.$lcItem])) {
            $method = 'is'.$item;
        } elseif (isset(self::$cache[$class]['methods']['__call'])) {
            $method = (string) $item;
            $call = true;
        } else {
            if ($isDefinedTest) {
                return false;
            }
            if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
                return;
            }
            throw new Twix_Error_Runtime(sprintf('Neither the property "%1$s" nor one of the methods "%1$s()", "get%1$s()"/"is%1$s()" or "__call()" exist and have public access in class "%2$s"', $item, get_class($object)), -1, $this->getTemplateName());
        }
        if ($isDefinedTest) {
            return true;
        }
        if ($this->env->hasExtension('sandbox')) {
            $this->env->getExtension('sandbox')->checkMethodAllowed($object, $method);
        }
        // Some objects throw exceptions when they have __call, and the method we try
        // to call is not supported. If ignoreStrictCheck is true, we should return null.
        try {
            $ret = call_user_func_array(array($object, $method), $arguments);
        } catch (BadMethodCallException $e) {
            if ($call && ($ignoreStrictCheck || !$this->env->isStrictVariables())) {
                return;
            }
            throw $e;
        }
        // useful when calling a template method from a template
        // this is not supported but unfortunately heavily used in the Symfony profiler
        if ($object instanceof Twix_TemplateInterface) {
            return $ret === '' ? '' : new Twix_Markup($ret, 'UTF-8');
        }
        return $ret;
    }
}

class Twix_Markup implements Countable
{
    protected $content;
    protected $charset;
    public function __construct($content, $charset)
    {
        $this->content = (string) $content;
        $this->charset = $charset;
    }
    public function __toString()
    {
        return $this->content;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return function_exists('mb_get_info') ? mb_strlen($this->content, $this->charset) : strlen($this->content);
    }
}

function twix_ensure_traversable($seq)
{
    if ($seq instanceof Traversable || is_array($seq)) {
        return $seq;
    }
    return array();
}

// add multibyte extensions if possible
function twix_length_filter($env, $thing)
{
    return is_scalar($thing) ? strlen($thing) : @count($thing);
}

function twix_title_string_filter($env, $string)
{
    return ucwords(strtolower($string));
}

function twix_capitalize_string_filter($env, $string)
{
    return ucfirst(strtolower($string));
}

function twix_array_batch($items, $size, $fill = null)
{
    if ($items instanceof Traversable) {
        $items = iterator_to_array($items, false);
    }
    $size = ceil($size);
    $result = array_chunk($items, $size, true);
    if (null !== $fill && !empty($result)) {
        $last = count($result) - 1;
        if ($fillCount = $size - count($result[$last])) {
            $result[$last] = array_merge(
                $result[$last],
                array_fill(0, $fillCount, $fill)
            );
        }
    }
    return $result;
}

function twix_round($value, $precision = 0, $method = 'common')
{
    if ('common' == $method) {
        return round($value, $precision);
    }
    if ('ceil' != $method && 'floor' != $method) {
        throw new Twix_Error_Runtime('The round filter only supports the "common", "ceil", and "floor" methods.');
    }
    return $method($value * pow(10, $precision)) / pow(10, $precision);
}

function twix_in_filter($value, $compare)
{
    if (is_array($compare)) {
        return in_array($value, $compare, is_object($value) || is_resource($value));
    } elseif (is_string($compare) && (is_string($value) || is_int($value) || is_float($value))) {
        return '' === $value || false !== strpos($compare, (string) $value);
    } elseif ($compare instanceof Traversable) {
        return in_array($value, iterator_to_array($compare, false), is_object($value) || is_resource($value));
    }
    return false;
}

function twix_slice($env, $item, $start, $length = null, $preserveKeys = false)
{
    if ($item instanceof Traversable) {
        if ($item instanceof IteratorAggregate) {
            $item = $item->getIterator();
        }
        if ($start >= 0 && $length >= 0 && $item instanceof Iterator) {
            try {
                return iterator_to_array(new LimitIterator($item, $start, $length === null ? -1 : $length), $preserveKeys);
            } catch (OutOfBoundsException $exception) {
                return array();
            }
        }
        $item = iterator_to_array($item, $preserveKeys);
    }
    if (is_array($item)) {
        return array_slice($item, $start, $length, $preserveKeys);
    }
    $item = (string) $item;
    if (function_exists('mb_get_info') && null !== $charset = "UTF-8") {
        return (string) mb_substr($item, $start, null === $length ? mb_strlen($item, $charset) - $start : $length, $charset);
    }
    return (string) (null === $length ? substr($item, $start) : substr($item, $start, $length));
}

function twix_escape_filter($env, $string, $strategy = 'html', $charset = null, $autoescape = false)
{
    if ($autoescape && $string instanceof Twix_Markup) {
        return $string;
    }
    if (!is_string($string)) {
        if (is_object($string) && method_exists($string, '__toString')) {
            $string = (string) $string;
        } elseif (in_array($strategy, array('html', 'js', 'css', 'html_attr', 'url'))) {
            return $string;
        }
    }
    if (null === $charset) $charset = "UTF-8";

    switch ($strategy) {
        case 'html':
            // see http://php.net/htmlspecialchars
            // Using a static variable to avoid initializing the array
            // each time the function is called. Moving the declaration on the
            // top of the function slow downs other escaping strategies.
            static $htmlspecialcharsCharsets;
            if (null === $htmlspecialcharsCharsets) {
                if (defined('HHVM_VERSION')) {
                    $htmlspecialcharsCharsets = array('utf-8' => true, 'UTF-8' => true);
                } else {
                    $htmlspecialcharsCharsets = array(
                        'ISO-8859-1' => true, 'ISO8859-1' => true,
                        'ISO-8859-15' => true, 'ISO8859-15' => true,
                        'utf-8' => true, 'UTF-8' => true,
                        'CP866' => true, 'IBM866' => true, '866' => true,
                        'CP1251' => true, 'WINDOWS-1251' => true, 'WIN-1251' => true,
                        '1251' => true,
                        'CP1252' => true, 'WINDOWS-1252' => true, '1252' => true,
                        'KOI8-R' => true, 'KOI8-RU' => true, 'KOI8R' => true,
                        'BIG5' => true, '950' => true,
                        'GB2312' => true, '936' => true,
                        'BIG5-HKSCS' => true,
                        'SHIFT_JIS' => true, 'SJIS' => true, '932' => true,
                        'EUC-JP' => true, 'EUCJP' => true,
                        'ISO8859-5' => true, 'ISO-8859-5' => true, 'MACROMAN' => true,
                    );
                }
            }
            if (isset($htmlspecialcharsCharsets[$charset])) {
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }
            if (isset($htmlspecialcharsCharsets[strtoupper($charset)])) {
                // cache the lowercase variant for future iterations
                $htmlspecialcharsCharsets[$charset] = true;
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }
            $string = twix_convert_encoding($string, 'UTF-8', $charset);
            $string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return twix_convert_encoding($string, $charset, 'UTF-8');
        case 'js':
            // escape all non-alphanumeric characters
            // into their \xHH or \uHHHH representations
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, 'UTF-8', $charset);
            }
            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Twix_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }
            $string = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su', '_twix_escape_js_callback', $string);
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, $charset, 'UTF-8');
            }
            return $string;
        case 'css':
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, 'UTF-8', $charset);
            }
            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Twix_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }
            $string = preg_replace_callback('#[^a-zA-Z0-9]#Su', '_twix_escape_css_callback', $string);
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, $charset, 'UTF-8');
            }
            return $string;
        case 'html_attr':
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, 'UTF-8', $charset);
            }
            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Twix_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }
            $string = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', '_twix_escape_html_attr_callback', $string);
            if ('UTF-8' !== $charset) {
                $string = twix_convert_encoding($string, $charset, 'UTF-8');
            }
            return $string;
        case 'url':
            if (PHP_VERSION_ID < 50300) {
                return str_replace('%7E', '~', rawurlencode($string));
            }
            return rawurlencode($string);
        default:
            static $escapers;
            if (null === $escapers) {
                $escapers = $env->getExtension('core')->getEscapers();
            }
            if (isset($escapers[$strategy])) {
                return call_user_func($escapers[$strategy], $env, $string, $charset);
            }
            $validStrategies = implode(', ', array_merge(array('html', 'js', 'url', 'css', 'html_attr'), array_keys($escapers)));
            throw new Twix_Error_Runtime(sprintf('Invalid escaping strategy "%s" (valid ones: %s).', $strategy, $validStrategies));
    }
}

if (function_exists('mb_convert_encoding')) {
    function twix_convert_encoding($string, $to, $from)
    {
        return mb_convert_encoding($string, $to, $from);
    }
} elseif (function_exists('iconv')) {
    function twix_convert_encoding($string, $to, $from)
    {
        return iconv($from, $to, $string);
    }
} else {
    function twix_convert_encoding($string, $to, $from)
    {
        throw new Twix_Error_Runtime('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
    }
}

function _twix_escape_js_callback($matches)
{
    $char = $matches[0];
    // \xHH
    if (!isset($char[1])) {
        return '\\x'.strtoupper(substr('00'.bin2hex($char), -2));
    }
    // \uHHHH
    $char = twix_convert_encoding($char, 'UTF-16BE', 'UTF-8');
    return '\\u'.strtoupper(substr('0000'.bin2hex($char), -4));
}
function _twix_escape_css_callback($matches)
{
    $char = $matches[0];
    // \xHH
    if (!isset($char[1])) {
        $hex = ltrim(strtoupper(bin2hex($char)), '0');
        if (0 === strlen($hex)) {
            $hex = '0';
        }
        return '\\'.$hex.' ';
    }
    // \uHHHH
    $char = twix_convert_encoding($char, 'UTF-16BE', 'UTF-8');
    return '\\'.ltrim(strtoupper(bin2hex($char)), '0').' ';
}
function _twix_escape_html_attr_callback($matches)
{

    static $entityMap = array(
        34 => 'quot', /* quotation mark */
        38 => 'amp',  /* ampersand */
        60 => 'lt',   /* less-than sign */
        62 => 'gt',   /* greater-than sign */
    );
    $chr = $matches[0];
    $ord = ord($chr);

    if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) {
        return '&#xFFFD;';
    }

    if (strlen($chr) == 1) {
        $hex = strtoupper(substr('00'.bin2hex($chr), -2));
    } else {
        $chr = twix_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
        $hex = strtoupper(substr('0000'.bin2hex($chr), -4));
    }
    $int = hexdec($hex);
    if (array_key_exists($int, $entityMap)) {
        return sprintf('&%s;', $entityMap[$int]);
    }

    return sprintf('&#x%s;', $hex);
}

function twix_replace_filter($str, $from, $to = null)
{
    if ($from instanceof Traversable) {
        $from = iterator_to_array($from);
    } elseif (is_string($from) && is_string($to)) {
        @trigger_error('Using "replace" with character by character replacement is deprecated since version 1.22 and will be removed in Twig 2.0', E_USER_DEPRECATED);
        return strtr($str, $from, $to);
    } elseif (!is_array($from)) {
        throw new Twix_Error_Runtime(sprintf('The "replace" filter expects an array or "Traversable" as replace values, got "%s".',is_object($from) ? get_class($from) : gettype($from)));
    }
    return strtr($str, $from);
}

function twix_test_iterable($value)
{
    return $value instanceof Traversable || is_array($value);
}

function twix_array_merge($arr1, $arr2)
{
    if ($arr1 instanceof Traversable) {
        $arr1 = iterator_to_array($arr1);
    } elseif (!is_array($arr1)) {
        throw new Twix_Error_Runtime(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as first argument.', gettype($arr1)));
    }
    if ($arr2 instanceof Traversable) {
        $arr2 = iterator_to_array($arr2);
    } elseif (!is_array($arr2)) {
        throw new Twix_Error_Runtime(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as second argument.', gettype($arr2)));
    }
    return array_merge($arr1, $arr2);
}

function twix_cycle($values, $position)
{
    if (!is_array($values) && !$values instanceof ArrayAccess) {
        return $values;
    }
    return $values[$position % count($values)];
}
function twix_random($env, $values = null)
{
    if (null === $values) {
        return mt_rand();
    }
    if (is_int($values) || is_float($values)) {
        return $values < 0 ? mt_rand($values, 0) : mt_rand(0, $values);
    }
    if ($values instanceof Traversable) {
        $values = iterator_to_array($values);
    } elseif (is_string($values)) {
        if ('' === $values) {
            return '';
        }
        if (null !== $charset = 'UTF-8') {
            if ('UTF-8' !== $charset) {
                $values = twix_convert_encoding($values, 'UTF-8', $charset);
            }
            // unicode version of str_split()
            // split at all positions, but not after the start and not before the end
            $values = preg_split('/(?<!^)(?!$)/u', $values);
            if ('UTF-8' !== $charset) {
                foreach ($values as $i => $value) {
                    $values[$i] = twix_convert_encoding($value, $charset, 'UTF-8');
                }
            }
        } else {
            return $values[mt_rand(0, strlen($values) - 1)];
        }
    }
    if (!is_array($values)) {
        return $values;
    }
    if (0 === count($values)) {
        throw new Twix_Error_Runtime('The random function cannot pick from an empty array.');
    }
    return $values[array_rand($values, 1)];
}
function twix_date_format_filter($env, $date, $format = null, $timezone = null)
{
    if (null === $format) {
        $formats = $env->getExtension('core')->getDateFormat();
        $format = $date instanceof DateInterval ? $formats[1] : $formats[0];
    }
    if ($date instanceof DateInterval) {
        return $date->format($format);
    }
    return twix_date_converter($env, $date, $timezone)->format($format);
}
function twix_date_modify_filter($env, $date, $modifier)
{
    $date = twix_date_converter($env, $date, false);
    $resultDate = $date->modify($modifier);
    // This is a hack to ensure PHP 5.2 support and support for DateTimeImmutable
    // DateTime::modify does not return the modified DateTime object < 5.3.0
    // and DateTimeImmutable does not modify $date.
    return null === $resultDate ? $date : $resultDate;
}
function twix_date_converter($env, $date = null, $timezone = null)
{
    // determine the timezone
    if (false !== $timezone) {
        if (null === $timezone) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        } elseif (!$timezone instanceof DateTimeZone) {
            $timezone = new DateTimeZone($timezone);
        }
    }
    // immutable dates
    if ($date instanceof DateTimeImmutable) {
        return false !== $timezone ? $date->setTimezone($timezone) : $date;
    }
    if ($date instanceof DateTime || $date instanceof DateTimeInterface) {
        $date = clone $date;
        if (false !== $timezone) {
            $date->setTimezone($timezone);
        }
        return $date;
    }
    if (null === $date || 'now' === $date) {
        return new DateTime($date, false !== $timezone ? $timezone : $env->getExtension('core')->getTimezone());
    }
    $asString = (string) $date;
    if (ctype_digit($asString) || (!empty($asString) && '-' === $asString[0] && ctype_digit(substr($asString, 1)))) {
        $date = new DateTime('@'.$date);
    } else {
        $date = new DateTime($date, new DateTimeZone(date_default_timezone_get()));
    }
    if (false !== $timezone) {
        $date->setTimezone($timezone);
    }
    return $date;
}

function twix_number_format_filter($env, $number, $decimal = null, $decimalPoint = null, $thousandSep = null)
{
    /*
    $defaults = $env->getExtension('core')->getNumberFormat();
    if (null === $decimal) {
        $decimal = $defaults[0];
    }
    if (null === $decimalPoint) {
        $decimalPoint = $defaults[1];
    }
    if (null === $thousandSep) {
        $thousandSep = $defaults[2];
    }
    */
    if (null === $decimal) { // MaxD
        $decimal = 2;
    }
    if (null === $decimalPoint) {
        $decimalPoint = '.';
    }
    if (null === $thousandSep) {
        $thousandSep = ',';
    }
    return number_format((float) $number, $decimal, $decimalPoint, $thousandSep);
}
function twix_urlencode_filter($url)
{
    if (is_array($url)) {
        if (defined('PHP_QUERY_RFC3986')) {
            return http_build_query($url, '', '&', PHP_QUERY_RFC3986);
        }
        return http_build_query($url, '', '&');
    }
    return rawurlencode($url);
}
if (PHP_VERSION_ID < 50300) {

    function twix_jsonencode_filter($value, $options = 0)
    {
        if ($value instanceof Twix_Markup) {
            $value = (string) $value;
        } elseif (is_array($value)) {
            array_walk_recursive($value, '_twix_markup2string');
        }
        return json_encode($value);
    }
} else {

    function twix_jsonencode_filter($value, $options = 0)
    {
        if ($value instanceof Twix_Markup) {
            $value = (string) $value;
        } elseif (is_array($value)) {
            array_walk_recursive($value, '_twix_markup2string');
        }
        return json_encode($value, $options);
    }
}
function _twix_markup2string(&$value)
{
    if ($value instanceof Twix_Markup) {
        $value = (string) $value;
    }
}

function twix_first($env, $item)
{
    $elements = twix_slice($env, $item, 0, 1, false);
    return is_string($elements) ? $elements : current($elements);
}
function twix_last($env, $item)
{
    $elements = twix_slice($env, $item, -1, 1, false);
    return is_string($elements) ? $elements : current($elements);
}
function twix_join_filter($value, $glue = '')
{
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value, false);
    }
    return implode($glue, (array) $value);
}
function twix_split_filter($env, $value, $delimiter, $limit = null)
{
    if (!empty($delimiter)) {
        return null === $limit ? explode($delimiter, $value) : explode($delimiter, $value, $limit);
    }
    $charset = 'UTF-8';
    if (!function_exists('mb_get_info')) {
        return str_split($value, null === $limit ? 1 : $limit);
    }
    if ($limit <= 1) {
        return preg_split('/(?<!^)(?!$)/u', $value);
    }
    $length = mb_strlen($value, $charset);
    if ($length < $limit) {
        return array($value);
    }
    $r = array();
    for ($i = 0; $i < $length; $i += $limit) {
        $r[] = mb_substr($value, $i, $limit, $charset);
    }
    return $r;
}
// The '_default' filter is used internally to avoid using the ternary operator
// which costs a lot for big contexts (before PHP 5.4). So, on average,
// a function call is cheaper.
function _twix_default_filter($value, $default = '')
{
    if (twix_test_empty($value)) {
        return $default;
    }
    return $value;
}
function twix_get_array_keys_filter($array)
{
    if ($array instanceof Traversable) {
        return array_keys(iterator_to_array($array));
    }
    if (!is_array($array)) {
        return array();
    }
    return array_keys($array);
}
function twix_reverse_filter($env, $item, $preserveKeys = false)
{
    if ($item instanceof Traversable) {
        return array_reverse(iterator_to_array($item), $preserveKeys);
    }
    if (is_array($item)) {
        return array_reverse($item, $preserveKeys);
    }
    if ($charset = 'UTF-8') {
        $string = (string) $item;
        if ('UTF-8' !== $charset) {
            $item = twix_convert_encoding($string, 'UTF-8', $charset);
        }
        preg_match_all('/./us', $item, $matches);
        $string = implode('', array_reverse($matches[0]));
        if ('UTF-8' !== $charset) {
            $string = twix_convert_encoding($string, $charset, 'UTF-8');
        }
        return $string;
    }
    return strrev((string) $item);
}
function twix_sort_filter($array)
{
    if ($array instanceof Traversable) {
        $array = iterator_to_array($array);
    } elseif (!is_array($array)) {
        throw new Twix_Error_Runtime(sprintf('The sort filter only works with arrays or "Traversable", got "%s".', gettype($array)));
    }
    asort($array);
    return $array;
}

function twix_escape_filter_is_safe(Twix_Node $filterArgs)
{
    foreach ($filterArgs as $arg) {
        if ($arg instanceof Twix_Node_Expression_Constant) {
            return array($arg->getAttribute('value'));
        }
        return array();
    }
    return array('html');
}

function twix_test_empty($value)
{
    if ($value instanceof Countable) {
        return 0 == count($value);
    }
    return '' === $value || false === $value || null === $value || array() === $value;
}

// Errors

class Twix_Error extends Exception
{
    protected $lineno;
    protected $filename;
    protected $rawMessage;
    protected $previous;

    public function __construct($message, $lineno = -1, $filename = null, Exception $previous = null)
    {
        if (PHP_VERSION_ID < 50300) {
            $this->previous = $previous;
            parent::__construct('');
        } else {
            parent::__construct('', 0, $previous);
        }

        $this->lineno = $lineno;
        $this->filename = $filename;

        if (-1 === $this->lineno || null === $this->filename) {
            $this->guessTemplateInfo();
        }

        $this->rawMessage = $message;

        $this->updateRepr();
    }

    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    public function getTemplateFile()
    {
        return $this->filename;
    }

    public function setTemplateFile($filename)
    {
        $this->filename = $filename;

        $this->updateRepr();
    }

    public function getTemplateLine()
    {
        return $this->lineno;
    }

    public function setTemplateLine($lineno)
    {
        $this->lineno = $lineno;

        $this->updateRepr();
    }

    public function guess()
    {
        $this->guessTemplateInfo();
        $this->updateRepr();
    }

    public function __call($method, $arguments)
    {
        if ('getprevious' == strtolower($method)) {
            return $this->previous;
        }

        throw new BadMethodCallException(sprintf('Method "twix_Error::%s()" does not exist.', $method));
    }

    public function appendMessage($rawMessage)
    {
        $this->rawMessage .= $rawMessage;
        $this->updateRepr();
    }

    protected function updateRepr()
    {
        $this->message = $this->rawMessage;

        $dot = false;
        if ('.' === substr($this->message, -1)) {
            $this->message = substr($this->message, 0, -1);
            $dot = true;
        }

        $questionMark = false;
        if ('?' === substr($this->message, -1)) {
            $this->message = substr($this->message, 0, -1);
            $questionMark = true;
        }

        if ($this->filename) {
            if (is_string($this->filename) || (is_object($this->filename) && method_exists($this->filename, '__toString'))) {
                $filename = sprintf('"%s"', $this->filename);
            } else {
                $filename = json_encode($this->filename);
            }
            $this->message .= sprintf(' in %s', $filename);
        }

        if ($this->lineno && $this->lineno >= 0) {
            $this->message .= sprintf(' at line %d', $this->lineno);
        }

        if ($dot) {
            $this->message .= '.';
        }

        if ($questionMark) {
            $this->message .= '?';
        }
    }

    protected function guessTemplateInfo()
    {
        $template = null;
        $templateClass = null;

        if (PHP_VERSION_ID >= 50306) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT);
        } else {
            $backtrace = debug_backtrace();
        }

        foreach ($backtrace as $trace) {
            if (isset($trace['object']) && $trace['object'] instanceof Twix_Template && 'Twix_Template' !== get_class($trace['object'])) {
                $currentClass = get_class($trace['object']);
                $isEmbedContainer = 0 === strpos($templateClass, $currentClass);
                if (null === $this->filename || ($this->filename == $trace['object']->getTemplateName() && !$isEmbedContainer)) {
                    $template = $trace['object'];
                    $templateClass = get_class($trace['object']);
                }
            }
        }

        // update template filename
        if (null !== $template && null === $this->filename) {
            $this->filename = $template->getTemplateName();
        }

        if (null === $template || $this->lineno > -1) {
            return;
        }

        $r = new ReflectionObject($template);
        $file = $r->getFileName();

        if (is_dir($file)) {
            $file = '';
        }

        $exceptions = array($e = $this);
        while (($e instanceof self || method_exists($e, 'getPrevious')) && $e = $e->getPrevious()) {
            $exceptions[] = $e;
        }

        while ($e = array_pop($exceptions)) {
            $traces = $e->getTrace();
            array_unshift($traces, array('file' => $e->getFile(), 'line' => $e->getLine()));

            while ($trace = array_shift($traces)) {
                if (!isset($trace['file']) || !isset($trace['line']) || $file != $trace['file']) {
                    continue;
                }

                foreach ($template->getDebugInfo() as $codeLine => $templateLine) {
                    if ($codeLine <= $trace['line']) {
                        // update template line
                        $this->lineno = $templateLine;

                        return;
                    }
                }
            }
        }
    }
}

class Twix_Error_Loader extends Twix_Error
{
    public function __construct($message, $lineno = -1, $filename = null, Exception $previous = null)
    {
        parent::__construct($message, false, false, $previous);
    }
}

class Twix_Error_Runtime extends Twix_Error
{
}

class Twix_Error_Syntax extends Twix_Error
{
    public function addSuggestions($name, array $items)
    {
        if (!$alternatives = self::computeAlternatives($name, $items)) {
            return;
        }

        $this->appendMessage(sprintf(' Did you mean "%s"?', implode('", "', $alternatives)));
    }

    public static function computeAlternatives($name, $items)
    {
        $alternatives = array();
        foreach ($items as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = $lev;
            }
        }
        asort($alternatives);

        return array_keys($alternatives);
    }
}