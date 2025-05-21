<?php

interface Twix_LoaderInterface
{
    
    public function getSource($name);
    
    public function getCacheKey($name);
    
    public function isFresh($name, $time);
}

interface Twix_ExistsLoaderInterface
{
    
    public function exists($name);
}

#[\AllowDynamicProperties]
class Twix_Loader_Filesystem implements Twix_LoaderInterface, Twix_ExistsLoaderInterface
{
    
    const MAIN_NAMESPACE = '__main__';
    protected $paths = array();
    protected $cache = array();
    protected $errorCache = array();
    
    public function __construct($paths = array(), $code = '')
    {
        if ($paths) {
            $this->setPaths($paths);
        }
        $this->code = $code;
    }
    
    public function getPaths($namespace = self::MAIN_NAMESPACE)
    {
        return isset($this->paths[$namespace]) ? $this->paths[$namespace] : array();
    }
    
    public function getNamespaces()
    {
        return array_keys($this->paths);
    }
    
    public function setPaths($paths, $namespace = self::MAIN_NAMESPACE)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $this->paths[$namespace] = array();
        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }
    
    public function addPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = $this->errorCache = array();
        if (!is_dir($path)) {
            throw new Twix_Error_Loader(sprintf('The "%s" directory does not exist.', $path));
        }
        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    public function prependPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = $this->errorCache = array();
        if (!is_dir($path)) {
            throw new Twix_Error_Loader(sprintf('The "%s" directory does not exist.', $path));
        }
        $path = rtrim($path, '/\\');
        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace][] = $path;
        } else {
            array_unshift($this->paths[$namespace], $path);
        }
    }

    public function getSource($name)
    {
        if ($this->code) {
            $this->real_name = "Event code replacing $name, CRC ". crc32($this->code);
            return $this->code;
        } //MaxD
        $file = $this->findTemplate($name);
        if( class_exists('VQMod') ) {
            $org = twix_if_inside($file, 'view/template/');
            $org = twix_if_inside($org, 'view/theme/');
            $file = \VQMod::modCheck($file, DIR_TEMPLATE.$org);
        }
        $this->real_name = $file;
        $src = file_get_contents($file); //MaxD
        return $src;
    }

    public function getCacheKey($name)
    {
        return $this->findTemplate($name);
    }

    public function exists($name)
    {
        $name = $this->normalizeName($name);
        if (isset($this->cache[$name])) {
            return true;
        }
        try {
            return false !== $this->findTemplate($name, false);
        } catch (Twix_Error_Loader $exception) {
            return false;
        }
    }

    public function isFresh($name, $time)
    {
        return filemtime($this->findTemplate($name)) <= $time;
    }
    protected function findTemplate($name)
    {
        $throw = func_num_args() > 1 ? func_get_arg(1) : true;
        $name = $this->normalizeName($name);
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        if (isset($this->errorCache[$name])) {
            if (!$throw) {
                return false;
            }
            throw new Twix_Error_Loader($this->errorCache[$name]);
        }
        $this->validateName($name);
        list($namespace, $shortname) = $this->parseName($name);
        if (!isset($this->paths[$namespace])) {
            $this->errorCache[$name] = sprintf('There are no registered paths for namespace "%s".', $namespace);
            if (!$throw) {
                return false;
            }
            throw new Twix_Error_Loader($this->errorCache[$name]);
        }
        foreach ($this->paths[$namespace] as $path) {
            if (is_file($path.'/'.$shortname)) {
                if (false !== $realpath = realpath($path.'/'.$shortname)) {
                    return $this->cache[$name] = $realpath;
                }
                return $this->cache[$name] = $path.'/'.$shortname;
            }
        }
        $this->errorCache[$name] = sprintf('Unable to find template "%s" (looked into: %s).', $name, implode(', ', $this->paths[$namespace]));
        if (!$throw) {
            return false;
        }
        throw new Twix_Error_Loader($this->errorCache[$name]);
    }
    protected function parseName($name, $default = self::MAIN_NAMESPACE)
    {
        if (isset($name[0]) && '@' == $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new Twix_Error_Loader(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
            }
            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);
            return array($namespace, $shortname);
        }
        return array($default, $name);
    }
    protected function normalizeName($name)
    {
        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', (string) $name));
    }
    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twix_Error_Loader('A template name cannot contain NUL bytes.');
        }
        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }
            if ($level < 0) {
                throw new Twix_Error_Loader(sprintf('Looks like you try to load a template outside configured directories (%s).', $name));
            }
        }
    }
}

function twix_replace($search=false, $replace='', $source=false)
{
    if (($p=strpos($search,'*'))!==false) {
        $before = substr($search,0,$p);
        $after = substr($search,$p+1);
        $r = twix_replace_inside("","",$before,$after,$source);
        $r = str_ireplace($before.$after,$replace,$r);
    } else
        $r = str_ireplace($search,$replace,$source);

    return $r;
}


function twix_replace_inside($find, $replace, $start, $end, $source) {

    $s=0;
    while (($s = stripos($source,$start,$s)) !==false) {

        $s += strlen($start);
        if ($end) $e = stripos($source,$end,$s);
        else $e = strlen($source);
        if ($e) {

            $left = substr($source,0,$s);
            $right = substr($source,$e);
            $mid = substr($source,$s,$e-$s);
            $midlen = strlen($mid);

            if ($find!=='') $mid = str_ireplace($find,$replace,$mid);
            else $mid = $replace;
            $source=$left.$mid.$right;
            $e = $e + strlen($mid) - $midlen;
            $s = $e + strlen($end);
            if ($s>strlen($source)) break;
        }
    }

    return $source;
}

class Twix_Environment
{
    const VERSION = '1.24.2-DEV';
    protected $charset;
    protected $loader;
    protected $debug;
    protected $autoReload;
    protected $cache;
    protected $lexer;
    protected $parser;
    protected $compiler;
    protected $baseTemplateClass;
    protected $extensions;
    protected $parsers;
    protected $visitors;
    protected $filters;
    protected $tests;
    protected $functions;
    protected $globals;
    protected $runtimeInitialized = false;
    protected $extensionInitialized = false;
    protected $loadedTemplates;
    protected $strictVariables;
    protected $unaryOperators;
    protected $binaryOperators;
    protected $templateClassPrefix = '__TwigTemplate_';
    protected $functionCallbacks = array();
    protected $filterCallbacks = array();
    protected $staging;
    private $originalCache;
    private $bcWriteCacheFile = false;
    private $bcGetCacheFilename = false;
    private $lastModifiedExtension = 0;

    public function __construct(Twix_LoaderInterface $loader = null, $options = array())
    {
        if (null !== $loader) {
            $this->setLoader($loader);
        } else {
            @trigger_error('Not passing a Twix_LoaderInterface as the first constructor argument of Twix_Environment is deprecated since version 1.21.', E_USER_DEPRECATED);
        }
        $options = array_merge(array(
            'debug' => false,
            'charset' => 'UTF-8',
            'base_template_class' => 'Twix_Template',
            'strict_variables' => false,
            'autoescape' => 'html',
            'cache' => false,
            'auto_reload' => null,
            'optimizations' => -1,
        ), $options);
        $this->debug = (bool) $options['debug'];
        $this->charset = strtoupper($options['charset']);
        $this->baseTemplateClass = $options['base_template_class'];
        $this->autoReload = null === $options['auto_reload'] ? $this->debug : (bool) $options['auto_reload'];
        $this->strictVariables = (bool) $options['strict_variables'];
        $this->setCache($options['cache']);
        $this->addExtension(new Twix_Extension_Core());
        $this->addExtension(new Twix_Extension_Escaper($options['autoescape']));
        $this->addExtension(new Twix_Extension_Optimizer($options['optimizations']));
        $this->staging = new Twix_Extension_Staging();
        // For BC
        if (is_string($this->originalCache)) {
            $r = new ReflectionMethod($this, 'writeCacheFile');
            if ($r->getDeclaringClass()->getName() !== __CLASS__) {
                @trigger_error('The Twix_Environment::writeCacheFile method is deprecated since version 1.22 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
                $this->bcWriteCacheFile = true;
            }
            $r = new ReflectionMethod($this, 'getCacheFilename');
            if ($r->getDeclaringClass()->getName() !== __CLASS__) {
                @trigger_error('The Twix_Environment::getCacheFilename method is deprecated since version 1.22 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
                $this->bcGetCacheFilename = true;
            }
        }
    }

    public function getBaseTemplateClass()
    {
        return $this->baseTemplateClass;
    }

    public function setBaseTemplateClass($class)
    {
        $this->baseTemplateClass = $class;
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    public function disableDebug()
    {
        $this->debug = false;
    }

    public function isDebug()
    {
        return $this->debug;
    }

    public function enableAutoReload()
    {
        $this->autoReload = true;
    }

    public function disableAutoReload()
    {
        $this->autoReload = false;
    }

    public function isAutoReload()
    {
        return $this->autoReload;
    }

    public function enableStrictVariables()
    {
        $this->strictVariables = true;
    }

    public function disableStrictVariables()
    {
        $this->strictVariables = false;
    }

    public function isStrictVariables()
    {
        return $this->strictVariables;
    }

    public function getCache($original = true)
    {
        return $original ? $this->originalCache : $this->cache;
    }

    public function setCache($cache)
    {
        if (is_string($cache)) {
            $this->originalCache = $cache;
            $this->cache = new Twix_Cache_Filesystem($cache);
        } elseif (false === $cache) {
            $this->originalCache = $cache;
            $this->cache = new Twix_Cache_Null();
        } elseif (null === $cache) {
            @trigger_error('Using "null" as the cache strategy is deprecated since version 1.23 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
            $this->originalCache = false;
            $this->cache = new Twix_Cache_Null();
        } elseif ($cache instanceof Twix_CacheInterface) {
            $this->originalCache = $this->cache = $cache;
        } else {
            throw new LogicException(sprintf('Cache can only be a string, false, or a Twix_CacheInterface implementation.'));
        }
    }

    public function getCacheFilename($name)
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        $key = $this->cache->generateKey($name, $this->getTemplateClass($name));
        return !$key ? false : $key;
    }

    public function getTemplateClass($name, $index = null)
    {
        $key = $this->getLoader()->getCacheKey($name);
        $key .= json_encode(array_keys($this->extensions));
        $key .= function_exists('twix_template_get_attributes');
        return $this->templateClassPrefix.hash('sha256', $key).(null === $index ? '' : '_'.$index);
    }

    public function getTemplateClassPrefix()
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        return $this->templateClassPrefix;
    }

    public function render($name, array $context = array())
    {
        return $this->loadTemplate($name)->render($context);
    }

    public function display($name, array $context = array())
    {
        $this->loadTemplate($name)->display($context);
    }

    public function loadTemplate($name, $index = null)
    {
        //$cls = $this->getTemplateClass($name, $index);
        $cls = str_replace('/', '__', str_replace('.twig', '', $name)); //MaxD2
        $cls = str_replace('-', '_', $cls);

        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }
        if (!class_exists($cls, false)) {
            if ($this->bcGetCacheFilename) {
                $key = $this->getCacheFilename($name);
            } else {
                $key = $this->cache->generateKey($name, $cls);
            }
            if (!$this->isAutoReload() || $this->isTemplateFresh($name, $this->cache->getTimestamp($key))) {
                $this->cache->load($key);
            }
            if (!class_exists($cls, false)) {

                $source = $this->getLoader()->getSource($name);
                $source = str_replace('context.', '', $source);

                $content = $this->compileSource($source, $name);

                // MX Replaces
                $content = substr($content, 0, strpos($content, '__Twig')).$cls.substr($content, strpos($content, ' extends')); //MaxD3
                $content = str_replace("construct(Twix_Environment ", "construct(", $content);
                $content = str_replace('twix_lower_filter($this->env, ', 'strtolower(', $content);
                $content = str_replace('twix_upper_filter($this->env, ', 'strtoupper(', $content);
                $content = str_replace('twix_jsonencode_filter(', 'json_encode(', $content);
                $content = str_replace('twix_constant(', 'constant(', $content);
                $content = str_replace('$this->env->mergeGlobals(', 'array_merge(', $content);
                $content = str_replace('$this->env->getCharset()', '""', $content);

                $content = str_replace('$this->loadTemplate(', 'echo twix_render(', $content);

                $content = str_replace(')->display(', ', ', $content);

                $content = str_replace('twix_test_empty(', 'empty(', $content);
                $content = str_replace('isset(@', 'isset(', $content);
                $content = str_replace('empty(@', 'empty(', $content);

                $content = str_replace(";\n?>", " ?>\n", $content);
                $content = str_replace("<?php\necho", '<?php echo', $content);

                $content = str_replace('/* _source_ */', '/* Twix ver. '.TWIX_VER.'  Source: '.$this->getLoader()->real_name.' */', $content);


                if ($this->bcWriteCacheFile) {
                    $this->writeCacheFile($key, $content);
                } else {
                    $this->cache->write($key, $content);
                }
                $this->cache->load($key); //MaxD
            }
        }
        if (!$this->runtimeInitialized) {
            $this->initRuntime();
        }
        return $this->loadedTemplates[$cls] = new $cls($this);
    }

    public function createTemplate($template)
    {
        $name = sprintf('__string_template__%s', hash('sha256', uniqid(mt_rand(), true), false));
        $loader = new Twix_Loader_Chain(array(
            new Twix_Loader_Array(array($name => $template)),
            $current = $this->getLoader(),
        ));
        $this->setLoader($loader);
        try {
            $template = $this->loadTemplate($name);
        } catch (Exception $e) {
            $this->setLoader($current);
            throw $e;
        } catch (Throwable $e) {
            $this->setLoader($current);
            throw $e;
        }
        $this->setLoader($current);
        return $template;
    }

    public function isTemplateFresh($name, $time)
    {
        if (0 === $this->lastModifiedExtension) {
            foreach ($this->extensions as $extension) {
                $r = new ReflectionObject($extension);
                if (is_file($r->getFileName()) && ($extensionTime = filemtime($r->getFileName())) > $this->lastModifiedExtension) {
                    $this->lastModifiedExtension = $extensionTime;
                }
            }
        }
        return $this->lastModifiedExtension <= $time && $this->getLoader()->isFresh($name, $time);
    }

    public function resolveTemplate($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }
        foreach ($names as $name) {
            if ($name instanceof Twix_Template) {
                return $name;
            }
            try {
                return $this->loadTemplate($name);
            } catch (Twix_Error_Loader $e) {
            }
        }
        if (1 === count($names)) {
            throw $e;
        }
        throw new Twix_Error_Loader(sprintf('Unable to find one of the following templates: "%s".', implode('", "', $names)));
    }
    
    public function clearTemplateCache()
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.18.3 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        $this->loadedTemplates = array();
    }
    
    public function clearCacheFiles()
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        if (is_string($this->originalCache)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->originalCache), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
                if ($file->isFile()) {
                    @unlink($file->getPathname());
                }
            }
        }
    }
    
    public function getLexer()
    {
        if (null === $this->lexer) {
            $this->lexer = new Twix_Lexer($this);
        }
        return $this->lexer;
    }
    
    public function setLexer(Twix_LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }
    
    public function tokenize($source, $name = null)
    {
        return $this->getLexer()->tokenize($source, $name);
    }
    
    public function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new Twix_Parser($this);
        }
        return $this->parser;
    }
    
    public function setParser(Twix_ParserInterface $parser)
    {
        $this->parser = $parser;
    }
    
    public function parse(Twix_TokenStream $stream)
    {
        return $this->getParser()->parse($stream);
    }
    
    public function getCompiler()
    {
        if (null === $this->compiler) {
            $this->compiler = new Twix_Compiler($this);
        }
        return $this->compiler;
    }
    
    public function setCompiler(Twix_CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }
    
    public function compile(Twix_NodeInterface $node)
    {
        return $this->getCompiler()->compile($node)->getSource();
    }
    
    public function compileSource($source, $name = null)
    {
        try {
            $compiled = $this->compile($this->parse($this->tokenize($source, $name)), $source);
            if (isset($source[0])) {
                //$compiled .= '/* '.str_replace(array('*/', "\r\n", "\r", "\n"), array('*//* ', "\n", "\n", "*/\n/* "), $source)."*/\n";
            }
            return $compiled;
        } catch (Twix_Error $e) {
            $e->setTemplateFile($name);
            throw $e;
        } catch (Exception $e) {
            throw new Twix_Error_Syntax(sprintf('An exception has been thrown during the compilation of a template ("%s").', $e->getMessage()), -1, $name, $e);
        }
    }
    
    public function setLoader(Twix_LoaderInterface $loader)
    {
        $this->loader = $loader;
    }
    
    public function getLoader()
    {
        if (null === $this->loader) {
            throw new LogicException('You must set a loader first.');
        }
        return $this->loader;
    }
    
    public function setCharset($charset)
    {
        $this->charset = strtoupper($charset);
    }
    
    public function getCharset()
    {
        return $this->charset;
    }
    
    public function initRuntime()
    {
        $this->runtimeInitialized = true;
        foreach ($this->getExtensions() as $name => $extension) {
            if (!$extension instanceof Twix_Extension_InitRuntimeInterface) {
                $m = new ReflectionMethod($extension, 'initRuntime');
                if ('Twix_Extension' !== $m->getDeclaringClass()->getName()) {
                    @trigger_error(sprintf('Defining the initRuntime() method in the "%s" extension is deprecated since version 1.23. Use the `needs_environment` option to get the Twix_Environment instance in filters, functions, or tests; or explicitly implement Twix_Extension_InitRuntimeInterface if needed (not recommended).', $name), E_USER_DEPRECATED);
                }
            }
            $extension->initRuntime($this);
        }
    }
    
    public function hasExtension($name)
    {
        return isset($this->extensions[$name]);
    }
    
    public function getExtension($name)
    {
        if (!isset($this->extensions[$name])) {
            throw new Twix_Error_Runtime(sprintf('The "%s" extension is not enabled.', $name));
        }
        return $this->extensions[$name];
    }
    
    public function addExtension(Twix_ExtensionInterface $extension)
    {
        $name = $extension->getName();
        if ($this->extensionInitialized) {
            throw new LogicException(sprintf('Unable to register extension "%s" as extensions have already been initialized.', $name));
        }
        if (isset($this->extensions[$name])) {
            @trigger_error(sprintf('The possibility to register the same extension twice ("%s") is deprecated since version 1.23 and will be removed in Twig 2.0. Use proper PHP inheritance instead.', $name), E_USER_DEPRECATED);
        }
        $this->lastModifiedExtension = 0;
        $this->extensions[$name] = $extension;
    }
    
    public function removeExtension($name)
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.12 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        if ($this->extensionInitialized) {
            throw new LogicException(sprintf('Unable to remove extension "%s" as extensions have already been initialized.', $name));
        }
        unset($this->extensions[$name]);
    }
    
    public function setExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }
    
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    public function addTokenParser(Twix_TokenParserInterface $parser)
    {
        if ($this->extensionInitialized) {
            throw new LogicException('Unable to add a token parser as extensions have already been initialized.');
        }
        $this->staging->addTokenParser($parser);
    }
    
    public function getTokenParsers()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->parsers;
    }
    
    public function getTags()
    {
        $tags = array();
        foreach ($this->getTokenParsers()->getParsers() as $parser) {
            if ($parser instanceof Twix_TokenParserInterface) {
                $tags[$parser->getTag()] = $parser;
            }
        }
        return $tags;
    }
    
    public function addNodeVisitor(Twix_NodeVisitorInterface $visitor)
    {
        if ($this->extensionInitialized) {
            throw new LogicException('Unable to add a node visitor as extensions have already been initialized.');
        }
        $this->staging->addNodeVisitor($visitor);
    }
    
    public function getNodeVisitors()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->visitors;
    }
    
    public function addFilter($name, $filter = null)
    {
        if (!$name instanceof Twix_SimpleFilter && !($filter instanceof Twix_SimpleFilter || $filter instanceof Twix_FilterInterface)) {
            throw new LogicException('A filter must be an instance of Twix_FilterInterface or Twix_SimpleFilter');
        }
        if ($name instanceof Twix_SimpleFilter) {
            $filter = $name;
            $name = $filter->getName();
        } else {
            @trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twix_SimpleFilter" instead when defining filter "%s".', __METHOD__, $name), E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new LogicException(sprintf('Unable to add filter "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addFilter($name, $filter);
    }
    
    public function getFilter($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }
        foreach ($this->filters as $pattern => $filter) {
            $pattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'), $count);
            if ($count) {
                if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
                    array_shift($matches);
                    $filter->setArguments($matches);
                    return $filter;
                }
            }
        }
        foreach ($this->filterCallbacks as $callback) {
            if (false !== $filter = call_user_func($callback, $name)) {
                return $filter;
            }
        }
        return false;
    }
    public function registerUndefinedFilterCallback($callable)
    {
        $this->filterCallbacks[] = $callable;
    }
    
    public function getFilters()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->filters;
    }
    
    public function addTest($name, $test = null)
    {
        if (!$name instanceof Twix_SimpleTest && !($test instanceof Twix_SimpleTest || $test instanceof Twix_TestInterface)) {
            throw new LogicException('A test must be an instance of Twix_TestInterface or Twix_SimpleTest');
        }
        if ($name instanceof Twix_SimpleTest) {
            $test = $name;
            $name = $test->getName();
        } else {
            @trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twix_SimpleTest" instead when defining test "%s".', __METHOD__, $name), E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new LogicException(sprintf('Unable to add test "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addTest($name, $test);
    }
    
    public function getTests()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->tests;
    }
    
    public function getTest($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->tests[$name])) {
            return $this->tests[$name];
        }
        return false;
    }
    
    public function addFunction($name, $function = null)
    {
        if (!$name instanceof Twix_SimpleFunction && !($function instanceof Twix_SimpleFunction || $function instanceof Twix_FunctionInterface)) {
            throw new LogicException('A function must be an instance of Twix_FunctionInterface or Twix_SimpleFunction');
        }
        if ($name instanceof Twix_SimpleFunction) {
            $function = $name;
            $name = $function->getName();
        } else {
            @trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twix_SimpleFunction" instead when defining function "%s".', __METHOD__, $name), E_USER_DEPRECATED);
        }
        if ($this->extensionInitialized) {
            throw new LogicException(sprintf('Unable to add function "%s" as extensions have already been initialized.', $name));
        }
        $this->staging->addFunction($name, $function);
    }
    
    public function getFunction($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }
        foreach ($this->functions as $pattern => $function) {
            $pattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'), $count);
            if ($count) {
                if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
                    array_shift($matches);
                    $function->setArguments($matches);
                    return $function;
                }
            }
        }
        foreach ($this->functionCallbacks as $callback) {
            if (false !== $function = call_user_func($callback, $name)) {
                return $function;
            }
        }
        if (function_exists($name)) return new Twix_SimpleFunction($name, $name); //MaxD
        return false;
    }
    public function registerUndefinedFunctionCallback($callable)
    {
        $this->functionCallbacks[] = $callable;
    }
    
    public function getFunctions()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->functions;
    }
    
    public function addGlobal($name, $value)
    {
        if ($this->extensionInitialized || $this->runtimeInitialized) {
            if (null === $this->globals) {
                $this->globals = $this->initGlobals();
            }
            if (!array_key_exists($name, $this->globals)) {
                // The deprecation notice must be turned into the following exception in Twig 2.0
                @trigger_error(sprintf('Registering global variable "%s" at runtime or when the extensions have already been initialized is deprecated since version 1.21.', $name), E_USER_DEPRECATED);
                //throw new LogicException(sprintf('Unable to add global "%s" as the runtime or the extensions have already been initialized.', $name));
            }
        }
        if ($this->extensionInitialized || $this->runtimeInitialized) {
            // update the value
            $this->globals[$name] = $value;
        } else {
            $this->staging->addGlobal($name, $value);
        }
    }
    
    public function getGlobals()
    {
        if (!$this->runtimeInitialized && !$this->extensionInitialized) {
            return $this->initGlobals();
        }
        if (null === $this->globals) {
            $this->globals = $this->initGlobals();
        }
        return $this->globals;
    }
    
    public function mergeGlobals(array $context)
    {
        // we don't use array_merge as the context being generally
        // bigger than globals, this code is faster.
        foreach ($this->getGlobals() as $key => $value) {
            if (!array_key_exists($key, $context)) {
                $context[$key] = $value;
            }
        }
        return $context;
    }
    
    public function getUnaryOperators()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->unaryOperators;
    }
    
    public function getBinaryOperators()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }
        return $this->binaryOperators;
    }
    
    public function computeAlternatives($name, $items)
    {
        @trigger_error(sprintf('The %s method is deprecated since version 1.23 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
        return Twix_Error_Syntax::computeAlternatives($name, $items);
    }
    
    protected function initGlobals()
    {
        $globals = array();
        foreach ($this->extensions as $name => $extension) {
            if (!$extension instanceof Twix_Extension_GlobalsInterface) {
                $m = new ReflectionMethod($extension, 'getGlobals');
                if ('Twix_Extension' !== $m->getDeclaringClass()->getName()) {
                    @trigger_error(sprintf('Defining the getGlobals() method in the "%s" extension without explicitly implementing Twix_Extension_GlobalsInterface is deprecated since version 1.23.', $name), E_USER_DEPRECATED);
                }
            }
            $extGlob = $extension->getGlobals();
            if (!is_array($extGlob)) {
                throw new UnexpectedValueException(sprintf('"%s::getGlobals()" must return an array of globals.', get_class($extension)));
            }
            $globals[] = $extGlob;
        }
        $globals[] = $this->staging->getGlobals();
        return call_user_func_array('array_merge', $globals);
    }
    
    protected function initExtensions()
    {
        if ($this->extensionInitialized) {
            return;
        }
        $this->extensionInitialized = true;
        $this->parsers = new Twix_TokenParserBroker(array(), array(), false);
        $this->filters = array();
        $this->functions = array();
        $this->tests = array();
        $this->visitors = array();
        $this->unaryOperators = array();
        $this->binaryOperators = array();
        foreach ($this->extensions as $extension) {
            $this->initExtension($extension);
        }
        $this->initExtension($this->staging);
    }
    
    protected function initExtension(Twix_ExtensionInterface $extension)
    {
        // filters
        foreach ($extension->getFilters() as $name => $filter) {
            if ($filter instanceof Twix_SimpleFilter) {
                $name = $filter->getName();
            } else {
                @trigger_error(sprintf('Using an instance of "%s" for filter "%s" is deprecated since version 1.21. Use Twix_SimpleFilter instead.', get_class($filter), $name), E_USER_DEPRECATED);
            }
            $this->filters[$name] = $filter;
        }
        // functions
        foreach ($extension->getFunctions() as $name => $function) {
            if ($function instanceof Twix_SimpleFunction) {
                $name = $function->getName();
            } else {
                @trigger_error(sprintf('Using an instance of "%s" for function "%s" is deprecated since version 1.21. Use Twix_SimpleFunction instead.', get_class($function), $name), E_USER_DEPRECATED);
            }
            $this->functions[$name] = $function;
        }
        // tests
        foreach ($extension->getTests() as $name => $test) {
            if ($test instanceof Twix_SimpleTest) {
                $name = $test->getName();
            } else {
                @trigger_error(sprintf('Using an instance of "%s" for test "%s" is deprecated since version 1.21. Use Twix_SimpleTest instead.', get_class($test), $name), E_USER_DEPRECATED);
            }
            $this->tests[$name] = $test;
        }
        // token parsers
        foreach ($extension->getTokenParsers() as $parser) {
            if ($parser instanceof Twix_TokenParserInterface) {
                $this->parsers->addTokenParser($parser);
            } elseif ($parser instanceof Twix_TokenParserBrokerInterface) {
                @trigger_error('Registering a Twix_TokenParserBrokerInterface instance is deprecated since version 1.21.', E_USER_DEPRECATED);
                $this->parsers->addTokenParserBroker($parser);
            } else {
                throw new LogicException('getTokenParsers() must return an array of Twix_TokenParserInterface or Twix_TokenParserBrokerInterface instances');
            }
        }
        // node visitors
        foreach ($extension->getNodeVisitors() as $visitor) {
            $this->visitors[] = $visitor;
        }
        // operators
        if ($operators = $extension->getOperators()) {
            if (2 !== count($operators)) {
                throw new InvalidArgumentException(sprintf('"%s::getOperators()" does not return a valid operators array.', get_class($extension)));
            }
            $this->unaryOperators = array_merge($this->unaryOperators, $operators[0]);
            $this->binaryOperators = array_merge($this->binaryOperators, $operators[1]);
        }
    }
    
    protected function writeCacheFile($file, $content)
    {
        $this->cache->write($file, $content);
    }
}

interface Twix_CacheInterface
{
    
    public function generateKey($name, $className);
    
    public function write($key, $content);
    
    public function load($key);
    
    public function getTimestamp($key);
}

class Twix_Cache_Filesystem implements Twix_CacheInterface
{
    const FORCE_BYTECODE_INVALIDATION = 1;
    private $directory;
    private $options;
    
    public function __construct($directory, $options = 0)
    {
        $this->directory = rtrim($directory, '\/').'/';
        $this->options = $options;
    }
    
    public function generateKey($name, $className)
    {
        $hash = hash('sha256', $className);
        return $this->directory.'twix/'.$className.'.php'; //MaxD2
    }
    
    public function load($key)
    {
        if (is_file($key)) {
            @include_once $key;
        }
    }
    
    public function write($key, $content)
    {
        $dir = dirname($key);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Unable to create the cache directory (%s).', $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new RuntimeException(sprintf('Unable to write in the cache directory (%s).', $dir));
        }
        $tmpFile = tempnam($dir, basename($key));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $key)) {
            @chmod($key, 0666 & ~umask());
            if (self::FORCE_BYTECODE_INVALIDATION == ($this->options & self::FORCE_BYTECODE_INVALIDATION)) {
                // Compile cached file into bytecode cache
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($key, true);
                } elseif (function_exists('apc_compile_file')) {
                    apc_compile_file($key);
                }
            }
            return;
        }
        throw new RuntimeException(sprintf('Failed to write cache file "%s".', $key));
    }
    
    public function getTimestamp($key)
    {
        if (!is_file($key)) {
            return 0;
        }
        return (int) @filemtime($key);
    }
}

interface Twix_ExtensionInterface
{
    
    public function initRuntime(Twix_Environment $environment);
    
    public function getTokenParsers();
    
    public function getNodeVisitors();
    
    public function getFilters();
    
    public function getTests();
    
    public function getFunctions();
    
    public function getOperators();
    
    public function getGlobals();
    
    public function getName();
}

abstract class Twix_Extension implements Twix_ExtensionInterface
{
    
    public function initRuntime(Twix_Environment $environment)
    {
    }
    
    public function getTokenParsers()
    {
        return array();
    }
    
    public function getNodeVisitors()
    {
        return array();
    }
    
    public function getFilters()
    {
        return array();
    }
    
    public function getTests()
    {
        return array();
    }
    
    public function getFunctions()
    {
        return array();
    }
    
    public function getOperators()
    {
        return array();
    }
    
    public function getGlobals()
    {
        return array();
    }
}

if (!defined('ENT_SUBSTITUTE')) {
    // use 0 as hhvm does not support several flags yet
    define('ENT_SUBSTITUTE', 0);
}
class Twix_Extension_Core extends Twix_Extension
{
    protected $dateFormats = array('F j, Y H:i', '%d days');
    protected $numberFormat = array(0, '.', ',');
    protected $timezone = null;
    protected $escapers = array();
    
    public function setEscaper($strategy, $callable)
    {
        $this->escapers[$strategy] = $callable;
    }
    
    public function getEscapers()
    {
        return $this->escapers;
    }
    
    public function setDateFormat($format = null, $dateIntervalFormat = null)
    {
        if (null !== $format) {
            $this->dateFormats[0] = $format;
        }
        if (null !== $dateIntervalFormat) {
            $this->dateFormats[1] = $dateIntervalFormat;
        }
    }
    
    public function getDateFormat()
    {
        return $this->dateFormats;
    }
    
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
    }
    
    public function getTimezone()
    {
        if (null === $this->timezone) {
            $this->timezone = new DateTimeZone(date_default_timezone_get());
        }
        return $this->timezone;
    }
    
    public function setNumberFormat($decimal, $decimalPoint, $thousandSep)
    {
        $this->numberFormat = array($decimal, $decimalPoint, $thousandSep);
    }
    
    public function getNumberFormat()
    {
        return $this->numberFormat;
    }
    public function getTokenParsers()
    {
        return array(
            new Twix_TokenParser_For(),
            new Twix_TokenParser_If(),
            new Twix_TokenParser_Extends(),
            new Twix_TokenParser_Include(),
            new Twix_TokenParser_Block(),
            new Twix_TokenParser_Use(),
            new Twix_TokenParser_Filter(),
            new Twix_TokenParser_Macro(),
            new Twix_TokenParser_Import(),
            new Twix_TokenParser_From(),
            new Twix_TokenParser_Set(),
            new Twix_TokenParser_Spaceless(),
            new Twix_TokenParser_Flush(),
            new Twix_TokenParser_Do(),
            new Twix_TokenParser_Embed(),
        );
    }
    public function getFilters()
    {
        $filters = array(
            // formatting filters
            new Twix_SimpleFilter('date', 'twix_date_format_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('date_modify', 'twix_date_modify_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('format', 'sprintf'),
            new Twix_SimpleFilter('replace', 'twix_replace_filter'),
            new Twix_SimpleFilter('number_format', 'twix_number_format_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('abs', 'abs'),
            new Twix_SimpleFilter('round', 'twix_round'),
            // encoding
            new Twix_SimpleFilter('url_encode', 'twix_urlencode_filter'),
            new Twix_SimpleFilter('json_encode', 'twix_jsonencode_filter'),
            new Twix_SimpleFilter('convert_encoding', 'twix_convert_encoding'),
            // string filters
            new Twix_SimpleFilter('title', 'twix_title_string_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('capitalize', 'twix_capitalize_string_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('upper', 'strtoupper'),
            new Twix_SimpleFilter('lower', 'strtolower'),
            new Twix_SimpleFilter('striptags', 'strip_tags'),
            new Twix_SimpleFilter('trim', 'trim'),
            new Twix_SimpleFilter('nl2br', 'nl2br', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            // array helpers
            new Twix_SimpleFilter('join', 'twix_join_filter'),
            new Twix_SimpleFilter('split', 'twix_split_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('sort', 'twix_sort_filter'),
            new Twix_SimpleFilter('merge', 'twix_array_merge'),
            new Twix_SimpleFilter('batch', 'twix_array_batch'),
            // string/array filters
            new Twix_SimpleFilter('reverse', 'twix_reverse_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('length', 'twix_length_filter', array('needs_environment' => true)),
            new Twix_SimpleFilter('slice', 'twix_slice', array('needs_environment' => true)),
            new Twix_SimpleFilter('first', 'twix_first', array('needs_environment' => true)),
            new Twix_SimpleFilter('last', 'twix_last', array('needs_environment' => true)),
            // iteration and runtime
            new Twix_SimpleFilter('default', '_twix_default_filter', array('node_class' => 'Twix_Node_Expression_Filter_Default')),
            new Twix_SimpleFilter('keys', 'twix_get_array_keys_filter'),
            // escaping
            new Twix_SimpleFilter('escape', 'twix_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twix_escape_filter_is_safe')),
            new Twix_SimpleFilter('e', 'twix_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twix_escape_filter_is_safe')),
        );
        if (function_exists('mb_get_info')) {
            $filters[] = new Twix_SimpleFilter('upper', 'twix_upper_filter', array('needs_environment' => true));
            $filters[] = new Twix_SimpleFilter('lower', 'twix_lower_filter', array('needs_environment' => true));
        }
        return $filters;
    }
    public function getFunctions()
    {
        return array(
            new Twix_SimpleFunction('constant', 'twix_constant'),
            new Twix_SimpleFunction('cycle', 'twix_cycle'),
            new Twix_SimpleFunction('random', 'twix_random', array('needs_environment' => true)),
            new Twix_SimpleFunction('date', 'twix_date_converter', array('needs_environment' => true)),
            new Twix_SimpleFunction('include', 'twix_include', array('needs_environment' => true, 'needs_context' => true, 'is_safe' => array('all'))),
            new Twix_SimpleFunction('source', 'twix_source', array('needs_environment' => true, 'is_safe' => array('all'))),
        );
    }
    public function getTests()
    {
        return array(
            new Twix_SimpleTest('even', null, array('node_class' => 'Twix_Node_Expression_Test_Even')),
            new Twix_SimpleTest('odd', null, array('node_class' => 'Twix_Node_Expression_Test_Odd')),
            new Twix_SimpleTest('defined', null, array('node_class' => 'Twix_Node_Expression_Test_Defined')),
            new Twix_SimpleTest('sameas', null, array('node_class' => 'Twix_Node_Expression_Test_Sameas', 'deprecated' => '1.21', 'alternative' => 'same as')),
            new Twix_SimpleTest('same as', null, array('node_class' => 'Twix_Node_Expression_Test_Sameas')),
            new Twix_SimpleTest('none', null, array('node_class' => 'Twix_Node_Expression_Test_Null')),
            new Twix_SimpleTest('null', null, array('node_class' => 'Twix_Node_Expression_Test_Null')),
            new Twix_SimpleTest('divisibleby', null, array('node_class' => 'Twix_Node_Expression_Test_Divisibleby', 'deprecated' => '1.21', 'alternative' => 'divisible by')),
            new Twix_SimpleTest('divisible by', null, array('node_class' => 'Twix_Node_Expression_Test_Divisibleby')),
            new Twix_SimpleTest('constant', null, array('node_class' => 'Twix_Node_Expression_Test_Constant')),
            new Twix_SimpleTest('empty', 'twix_test_empty'),
            new Twix_SimpleTest('iterable', 'twix_test_iterable'),
        );
    }
    public function getOperators()
    {
        return array(
            array(
                'not' => array('precedence' => 50, 'class' => 'Twix_Node_Expression_Unary_Not'),
                '-' => array('precedence' => 500, 'class' => 'Twix_Node_Expression_Unary_Neg'),
                '+' => array('precedence' => 500, 'class' => 'Twix_Node_Expression_Unary_Pos'),
            ),
            array(
                'or' => array('precedence' => 10, 'class' => 'Twix_Node_Expression_Binary_Or', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'and' => array('precedence' => 15, 'class' => 'Twix_Node_Expression_Binary_And', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'b-or' => array('precedence' => 16, 'class' => 'Twix_Node_Expression_Binary_BitwiseOr', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'b-xor' => array('precedence' => 17, 'class' => 'Twix_Node_Expression_Binary_BitwiseXor', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'b-and' => array('precedence' => 18, 'class' => 'Twix_Node_Expression_Binary_BitwiseAnd', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '==' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_Equal', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '!=' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_NotEqual', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '<' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_Less', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '>' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_Greater', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '>=' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_GreaterEqual', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '<=' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_LessEqual', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'not in' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_NotIn', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'in' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_In', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'matches' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_Matches', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'starts with' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_StartsWith', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'ends with' => array('precedence' => 20, 'class' => 'Twix_Node_Expression_Binary_EndsWith', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '..' => array('precedence' => 25, 'class' => 'Twix_Node_Expression_Binary_Range', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '+' => array('precedence' => 30, 'class' => 'Twix_Node_Expression_Binary_Add', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '-' => array('precedence' => 30, 'class' => 'Twix_Node_Expression_Binary_Sub', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '~' => array('precedence' => 40, 'class' => 'Twix_Node_Expression_Binary_Concat', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '*' => array('precedence' => 60, 'class' => 'Twix_Node_Expression_Binary_Mul', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '/' => array('precedence' => 60, 'class' => 'Twix_Node_Expression_Binary_Div', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '//' => array('precedence' => 60, 'class' => 'Twix_Node_Expression_Binary_FloorDiv', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '%' => array('precedence' => 60, 'class' => 'Twix_Node_Expression_Binary_Mod', 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'is' => array('precedence' => 100, 'callable' => array($this, 'parseTestExpression'), 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                'is not' => array('precedence' => 100, 'callable' => array($this, 'parseNotTestExpression'), 'associativity' => Twix_ExpressionParser::OPERATOR_LEFT),
                '**' => array('precedence' => 200, 'class' => 'Twix_Node_Expression_Binary_Power', 'associativity' => Twix_ExpressionParser::OPERATOR_RIGHT),
                '??' => array('precedence' => 300, 'class' => 'Twix_Node_Expression_NullCoalesce', 'associativity' => Twix_ExpressionParser::OPERATOR_RIGHT),
            ),
        );
    }
    public function parseNotTestExpression(Twix_Parser $parser, Twix_NodeInterface $node)
    {
        return new Twix_Node_Expression_Unary_Not($this->parseTestExpression($parser, $node), $parser->getCurrentToken()->getLine());
    }
    public function parseTestExpression(Twix_Parser $parser, Twix_NodeInterface $node)
    {
        $stream = $parser->getStream();
        list($name, $test) = $this->getTest($parser, $node->getLine());
        if ($test instanceof Twix_SimpleTest && $test->isDeprecated()) {
            $message = sprintf('Twig Test "%s" is deprecated', $name);
            if (!is_bool($test->getDeprecatedVersion())) {
                $message .= sprintf(' since version %s', $test->getDeprecatedVersion());
            }
            if ($test->getAlternative()) {
                $message .= sprintf('. Use "%s" instead', $test->getAlternative());
            }
            $message .= sprintf(' in %s at line %d.', $stream->getFilename(), $stream->getCurrent()->getLine());
            @trigger_error($message, E_USER_DEPRECATED);
        }
        $class = $this->getTestNodeClass($parser, $test);
        $arguments = null;
        if ($stream->test(Twix_Token::PUNCTUATION_TYPE, '(')) {
            $arguments = $parser->getExpressionParser()->parseArguments(true);
        }
        return new $class($node, $name, $arguments, $parser->getCurrentToken()->getLine());
    }
    protected function getTest(Twix_Parser $parser, $line)
    {
        $stream = $parser->getStream();
        $name = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
        $env = $parser->getEnvironment();
        if ($test = $env->getTest($name)) {
            return array($name, $test);
        }
        if ($stream->test(Twix_Token::NAME_TYPE)) {
            // try 2-words tests
            $name = $name.' '.$parser->getCurrentToken()->getValue();
            if ($test = $env->getTest($name)) {
                $parser->getStream()->next();
                return array($name, $test);
            }
        }
        $e = new Twix_Error_Syntax(sprintf('Unknown "%s" test.', $name), $line, $parser->getFilename());
        $e->addSuggestions($name, array_keys($env->getTests()));
        throw $e;
    }
    protected function getTestNodeClass(Twix_Parser $parser, $test)
    {
        if ($test instanceof Twix_SimpleTest) {
            return $test->getNodeClass();
        }
        return $test instanceof Twix_Test_Node ? $test->getClass() : 'Twix_Node_Expression_Test';
    }
    public function getName()
    {
        return 'core';
    }
}

function twix_source(Twix_Environment $env, $name, $ignoreMissing = false)
{
    try {
        return $env->getLoader()->getSource($name);
    } catch (Twix_Error_Loader $e) {
        if (!$ignoreMissing) {
            throw $e;
        }
    }
}
function twix_constant($constant, $object = null)
{
    if (null !== $object) {
        $constant = get_class($object).'::'.$constant;
    }
    return constant($constant);
}


class Twix_Extension_Escaper extends Twix_Extension
{
    protected $defaultStrategy;
    
    public function __construct($defaultStrategy = 'html')
    {
        $this->setDefaultStrategy($defaultStrategy);
    }
    public function getTokenParsers()
    {
        return array(new Twix_TokenParser_AutoEscape());
    }
    public function getNodeVisitors()
    {
        return array(new Twix_NodeVisitor_Escaper());
    }
    public function getFilters()
    {
        return array(
            new Twix_SimpleFilter('raw', 'twix_raw_filter', array('is_safe' => array('all'))),
        );
    }
    
    public function setDefaultStrategy($defaultStrategy)
    {
        // for BC
        if (true === $defaultStrategy) {
            @trigger_error('Using "true" as the default strategy is deprecated since version 1.21. Use "html" instead.', E_USER_DEPRECATED);
            $defaultStrategy = 'html';
        }
        if ('filename' === $defaultStrategy) {
            $defaultStrategy = array('Twix_FileExtensionEscapingStrategy', 'guess');
        }
        $this->defaultStrategy = $defaultStrategy;
    }
    
    public function getDefaultStrategy($filename)
    {
        // disable string callables to avoid calling a function named html or js,
        // or any other upcoming escaping strategy
        if (!is_string($this->defaultStrategy) && false !== $this->defaultStrategy) {
            return call_user_func($this->defaultStrategy, $filename);
        }
        return $this->defaultStrategy;
    }
    public function getName()
    {
        return 'escaper';
    }
}
function twix_raw_filter($string)
{
    return $string;
}

class Twix_Extension_Optimizer extends Twix_Extension
{
    protected $optimizers;
    public function __construct($optimizers = -1)
    {
        $this->optimizers = $optimizers;
    }
    public function getNodeVisitors()
    {
        return array(new Twix_NodeVisitor_Optimizer($this->optimizers));
    }
    public function getName()
    {
        return 'optimizer';
    }
}

class Twix_Extension_Staging extends Twix_Extension
{
    protected $functions = array();
    protected $filters = array();
    protected $visitors = array();
    protected $tokenParsers = array();
    protected $globals = array();
    protected $tests = array();
    public function addFunction($name, $function)
    {
        $this->functions[$name] = $function;
    }
    public function getFunctions()
    {
        return $this->functions;
    }
    public function addFilter($name, $filter)
    {
        $this->filters[$name] = $filter;
    }
    public function getFilters()
    {
        return $this->filters;
    }
    public function addNodeVisitor(Twix_NodeVisitorInterface $visitor)
    {
        $this->visitors[] = $visitor;
    }
    public function getNodeVisitors()
    {
        return $this->visitors;
    }
    public function addTokenParser(Twix_TokenParserInterface $parser)
    {
        $this->tokenParsers[] = $parser;
    }
    public function getTokenParsers()
    {
        return $this->tokenParsers;
    }
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }
    public function getGlobals()
    {
        return $this->globals;
    }
    public function addTest($name, $test)
    {
        $this->tests[$name] = $test;
    }
    public function getTests()
    {
        return $this->tests;
    }
    public function getName()
    {
        return 'staging';
    }
}

interface Twix_LexerInterface
{
    
    public function tokenize($code, $filename = null);
}

class Twix_Lexer implements Twix_LexerInterface
{
    protected $tokens;
    protected $code;
    protected $cursor;
    protected $lineno;
    protected $end;
    protected $state;
    protected $states;
    protected $brackets;
    protected $env;
    protected $filename;
    protected $options;
    protected $regexes;
    protected $position;
    protected $positions;
    protected $currentVarBlockLine;
    const STATE_DATA = 0;
    const STATE_BLOCK = 1;
    const STATE_VAR = 2;
    const STATE_STRING = 3;
    const STATE_INTERPOLATION = 4;
    const REGEX_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A';
    const REGEX_NUMBER = '/[0-9]+(?:\.[0-9]+)?/A';
    const REGEX_STRING = '/"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/As';
    const REGEX_DQ_STRING_DELIM = '/"/A';
    const REGEX_DQ_STRING_PART = '/[^#"\\\\]*(?:(?:\\\\.|#(?!\{))[^#"\\\\]*)*/As';
    const PUNCTUATION = '()[]{}?:.,|';
    public function __construct(Twix_Environment $env, array $options = array())
    {
        $this->env = $env;
        $this->options = array_merge(array(
            'tag_comment' => array('{#', '#}'),
            'tag_block' => array('{%', '%}'),
            'tag_variable' => array('{{', '}}'),
            'whitespace_trim' => '-',
            'interpolation' => array('#{', '}'),
        ), $options);
        $this->regexes = array(
            'lex_var' => '/\s*'.preg_quote($this->options['whitespace_trim'].$this->options['tag_variable'][1], '/').'\s*|\s*'.preg_quote($this->options['tag_variable'][1], '/').'/A',
            'lex_block' => '/\s*(?:'.preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '/').'\s*|\s*'.preg_quote($this->options['tag_block'][1], '/').')\n?/A',
            'lex_raw_data' => '/('.preg_quote($this->options['tag_block'][0].$this->options['whitespace_trim'], '/').'|'.preg_quote($this->options['tag_block'][0], '/').')\s*(?:end%s)\s*(?:'.preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '/').'\s*|\s*'.preg_quote($this->options['tag_block'][1], '/').')/s',
            'operator' => $this->getOperatorRegex(),
            'lex_comment' => '/(?:'.preg_quote($this->options['whitespace_trim'], '/').preg_quote($this->options['tag_comment'][1], '/').'\s*|'.preg_quote($this->options['tag_comment'][1], '/').')\n?/s',
            'lex_block_raw' => '/\s*(raw|verbatim)\s*(?:'.preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '/').'\s*|\s*'.preg_quote($this->options['tag_block'][1], '/').')/As',
            'lex_block_line' => '/\s*line\s+(\d+)\s*'.preg_quote($this->options['tag_block'][1], '/').'/As',
            'lex_tokens_start' => '/('.preg_quote($this->options['tag_variable'][0], '/').'|'.preg_quote($this->options['tag_block'][0], '/').'|'.preg_quote($this->options['tag_comment'][0], '/').')('.preg_quote($this->options['whitespace_trim'], '/').')?/s',
            'interpolation_start' => '/'.preg_quote($this->options['interpolation'][0], '/').'\s*/A',
            'interpolation_end' => '/\s*'.preg_quote($this->options['interpolation'][1], '/').'/A',
        );
    }
    
    public function tokenize($code, $filename = null)
    {
        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        } else {
            $mbEncoding = null;
        }
        $this->code = str_replace(array("\r\n", "\r"), "\n", $code);
        $this->filename = $filename;
        $this->cursor = 0;
        $this->lineno = 1;
        $this->end = strlen($this->code);
        $this->tokens = array();
        $this->state = self::STATE_DATA;
        $this->states = array();
        $this->brackets = array();
        $this->position = -1;
        // find all token starts in one go
        preg_match_all($this->regexes['lex_tokens_start'], $this->code, $matches, PREG_OFFSET_CAPTURE);
        $this->positions = $matches;
        while ($this->cursor < $this->end) {
            // dispatch to the lexing functions depending
            // on the current state
            switch ($this->state) {
                case self::STATE_DATA:
                    $this->lexData();
                    break;
                case self::STATE_BLOCK:
                    $this->lexBlock();
                    break;
                case self::STATE_VAR:
                    $this->lexVar();
                    break;
                case self::STATE_STRING:
                    $this->lexString();
                    break;
                case self::STATE_INTERPOLATION:
                    $this->lexInterpolation();
                    break;
            }
        }
        $this->pushToken(Twix_Token::EOF_TYPE);
        if (!empty($this->brackets)) {
            list($expect, $lineno) = array_pop($this->brackets);
            throw new Twix_Error_Syntax(sprintf('Unclosed "%s".', $expect), $lineno, $this->filename);
        }
        if ($mbEncoding) {
            mb_internal_encoding($mbEncoding);
        }
        return new Twix_TokenStream($this->tokens, $this->filename);
    }
    protected function lexData()
    {
        // if no matches are left we return the rest of the template as simple text token
        if ($this->position == count($this->positions[0]) - 1) {
            $this->pushToken(Twix_Token::TEXT_TYPE, substr($this->code, $this->cursor));
            $this->cursor = $this->end;
            return;
        }
        // Find the first token after the current cursor
        $position = $this->positions[0][++$this->position];
        while ($position[1] < $this->cursor) {
            if ($this->position == count($this->positions[0]) - 1) {
                return;
            }
            $position = $this->positions[0][++$this->position];
        }
        // push the template text first
        $text = $textContent = substr($this->code, $this->cursor, $position[1] - $this->cursor);
        if (isset($this->positions[2][$this->position][0]) && ($this->options['whitespace_trim'] === $this->positions[2][$this->position][0])) { //MaxD 7.4 fix
            $text = rtrim($text);
        }
        $this->pushToken(Twix_Token::TEXT_TYPE, $text);
        $this->moveCursor($textContent.$position[0]);
        switch ($this->positions[1][$this->position][0]) {
            case $this->options['tag_comment'][0]:
                $this->lexComment();
                break;
            case $this->options['tag_block'][0]:
                // raw data?
                if (preg_match($this->regexes['lex_block_raw'], $this->code, $match, 0, $this->cursor)) {
                    $this->moveCursor($match[0]);
                    $this->lexRawData($match[1]);
                // {% line \d+ %}
                } elseif (preg_match($this->regexes['lex_block_line'], $this->code, $match, 0, $this->cursor)) {
                    $this->moveCursor($match[0]);
                    $this->lineno = (int) $match[1];
                } else {
                    $this->pushToken(Twix_Token::BLOCK_START_TYPE);
                    $this->pushState(self::STATE_BLOCK);
                    $this->currentVarBlockLine = $this->lineno;
                }
                break;
            case $this->options['tag_variable'][0]:
                $this->pushToken(Twix_Token::VAR_START_TYPE);
                $this->pushState(self::STATE_VAR);
                $this->currentVarBlockLine = $this->lineno;
                break;
        }
    }
    protected function lexBlock()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_block'], $this->code, $match, 0, $this->cursor)) {
            $this->pushToken(Twix_Token::BLOCK_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
    protected function lexVar()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_var'], $this->code, $match, 0, $this->cursor)) {
            $this->pushToken(Twix_Token::VAR_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
    protected function lexExpression()
    {
        // whitespace
        if (preg_match('/\s+/A', $this->code, $match, 0, $this->cursor)) {
            $this->moveCursor($match[0]);
            if ($this->cursor >= $this->end) {
                throw new Twix_Error_Syntax(sprintf('Unclosed "%s".', $this->state === self::STATE_BLOCK ? 'block' : 'variable'), $this->currentVarBlockLine, $this->filename);
            }
        }
        // operators
        if (preg_match($this->regexes['operator'], $this->code, $match, 0, $this->cursor)) {
            $this->pushToken(Twix_Token::OPERATOR_TYPE, preg_replace('/\s+/', ' ', $match[0]));
            $this->moveCursor($match[0]);
        }
        // names
        elseif (preg_match(self::REGEX_NAME, $this->code, $match, 0, $this->cursor)) {
            $this->pushToken(Twix_Token::NAME_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        }
        // numbers
        elseif (preg_match(self::REGEX_NUMBER, $this->code, $match, 0, $this->cursor)) {
            $number = (float) $match[0];  // floats
            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                $number = (int) $match[0]; // integers lower than the maximum
            }
            $this->pushToken(Twix_Token::NUMBER_TYPE, $number);
            $this->moveCursor($match[0]);
        }
        // punctuation
        elseif (false !== strpos(self::PUNCTUATION, $this->code[$this->cursor])) {
            // opening bracket
            if (false !== strpos('([{', $this->code[$this->cursor])) {
                $this->brackets[] = array($this->code[$this->cursor], $this->lineno);
            }
            // closing bracket
            elseif (false !== strpos(')]}', $this->code[$this->cursor])) {
                if (empty($this->brackets)) {
                    throw new Twix_Error_Syntax(sprintf('Unexpected "%s".', $this->code[$this->cursor]), $this->lineno, $this->filename);
                }
                list($expect, $lineno) = array_pop($this->brackets);
                if ($this->code[$this->cursor] != strtr($expect, '([{', ')]}')) {
                    throw new Twix_Error_Syntax(sprintf('Unclosed "%s".', $expect), $lineno, $this->filename);
                }
            }
            $this->pushToken(Twix_Token::PUNCTUATION_TYPE, $this->code[$this->cursor]);
            ++$this->cursor;
        }
        // strings
        elseif (preg_match(self::REGEX_STRING, $this->code, $match, 0, $this->cursor)) {
            $this->pushToken(Twix_Token::STRING_TYPE, stripcslashes(substr($match[0], 1, -1)));
            $this->moveCursor($match[0]);
        }
        // opening double quoted string
        elseif (preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            $this->brackets[] = array('"', $this->lineno);
            $this->pushState(self::STATE_STRING);
            $this->moveCursor($match[0]);
        }
        // unlexable
        else {
            throw new Twix_Error_Syntax(sprintf('Unexpected character "%s".', $this->code[$this->cursor]), $this->lineno, $this->filename);
        }
    }
    protected function lexRawData($tag)
    {
        if ('raw' === $tag) {
            @trigger_error(sprintf('Twig Tag "raw" is deprecated since version 1.21. Use "verbatim" instead in %s at line %d.', $this->filename, $this->lineno), E_USER_DEPRECATED);
        }
        if (!preg_match(str_replace('%s', $tag, $this->regexes['lex_raw_data']), $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor)) {
            throw new Twix_Error_Syntax(sprintf('Unexpected end of file: Unclosed "%s" block.', $tag), $this->lineno, $this->filename);
        }
        $text = substr($this->code, $this->cursor, $match[0][1] - $this->cursor);
        $this->moveCursor($text.$match[0][0]);
        if (false !== strpos($match[1][0], $this->options['whitespace_trim'])) {
            $text = rtrim($text);
        }
        $this->pushToken(Twix_Token::TEXT_TYPE, $text);
    }
    protected function lexComment()
    {
        if (!preg_match($this->regexes['lex_comment'], $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor)) {
            throw new Twix_Error_Syntax('Unclosed comment.', $this->lineno, $this->filename);
        }
        $this->moveCursor(substr($this->code, $this->cursor, $match[0][1] - $this->cursor).$match[0][0]);
    }
    protected function lexString()
    {
        if (preg_match($this->regexes['interpolation_start'], $this->code, $match, 0, $this->cursor)) {
            $this->brackets[] = array($this->options['interpolation'][0], $this->lineno);
            $this->pushToken(Twix_Token::INTERPOLATION_START_TYPE);
            $this->moveCursor($match[0]);
            $this->pushState(self::STATE_INTERPOLATION);
        } elseif (preg_match(self::REGEX_DQ_STRING_PART, $this->code, $match, 0, $this->cursor) && strlen($match[0]) > 0) {
            $this->pushToken(Twix_Token::STRING_TYPE, stripcslashes($match[0]));
            $this->moveCursor($match[0]);
        } elseif (preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            list($expect, $lineno) = array_pop($this->brackets);
            if ($this->code[$this->cursor] != '"') {
                throw new Twix_Error_Syntax(sprintf('Unclosed "%s".', $expect), $lineno, $this->filename);
            }
            $this->popState();
            ++$this->cursor;
        }
    }
    protected function lexInterpolation()
    {
        $bracket = end($this->brackets);
        if ($this->options['interpolation'][0] === $bracket[0] && preg_match($this->regexes['interpolation_end'], $this->code, $match, 0, $this->cursor)) {
            array_pop($this->brackets);
            $this->pushToken(Twix_Token::INTERPOLATION_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
    protected function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (Twix_Token::TEXT_TYPE === $type && '' === $value) {
            return;
        }
        $this->tokens[] = new Twix_Token($type, $value, $this->lineno);
    }
    protected function moveCursor($text)
    {
        $this->cursor += strlen($text);
        $this->lineno += substr_count($text, "\n");
    }
    protected function getOperatorRegex()
    {
        $operators = array_merge(
            array('='),
            array_keys($this->env->getUnaryOperators()),
            array_keys($this->env->getBinaryOperators())
        );
        $operators = array_combine($operators, array_map('strlen', $operators));
        arsort($operators);
        $regex = array();
        foreach ($operators as $operator => $length) {
            // an operator that ends with a character must be followed by
            // a whitespace or a parenthesis
            if (ctype_alpha($operator[$length - 1])) {
                $r = preg_quote($operator, '/').'(?=[\s()])';
            } else {
                $r = preg_quote($operator, '/');
            }
            // an operator with a space can be any amount of whitespaces
            $r = preg_replace('/\s+/', '\s+', $r);
            $regex[] = $r;
        }
        return '/'.implode('|', $regex).'/A';
    }
    protected function pushState($state)
    {
        $this->states[] = $this->state;
        $this->state = $state;
    }
    protected function popState()
    {
        if (0 === count($this->states)) {
            throw new Exception('Cannot pop state without a previous state');
        }
        $this->state = array_pop($this->states);
    }
}

interface Twix_TokenParserBrokerInterface
{
    
    public function getTokenParser($tag);
    
    public function setParser(Twix_ParserInterface $parser);
    
    public function getParser();
}

class Twix_TokenParserBroker implements Twix_TokenParserBrokerInterface
{
    protected $parser;
    protected $parsers = array();
    protected $brokers = array();
    
    public function __construct($parsers = array(), $brokers = array(), $triggerDeprecationError = true)
    {
        if ($triggerDeprecationError) {
            @trigger_error('The '.__CLASS__.' class is deprecated since version 1.12 and will be removed in 2.0.', E_USER_DEPRECATED);
        }
        foreach ($parsers as $parser) {
            if (!$parser instanceof Twix_TokenParserInterface) {
                throw new LogicException('$parsers must a an array of Twix_TokenParserInterface.');
            }
            $this->parsers[$parser->getTag()] = $parser;
        }
        foreach ($brokers as $broker) {
            if (!$broker instanceof Twix_TokenParserBrokerInterface) {
                throw new LogicException('$brokers must a an array of Twix_TokenParserBrokerInterface.');
            }
            $this->brokers[] = $broker;
        }
    }
    
    public function addTokenParser(Twix_TokenParserInterface $parser)
    {
        $this->parsers[$parser->getTag()] = $parser;
    }
    
    public function removeTokenParser(Twix_TokenParserInterface $parser)
    {
        $name = $parser->getTag();
        if (isset($this->parsers[$name]) && $parser === $this->parsers[$name]) {
            unset($this->parsers[$name]);
        }
    }
    
    public function addTokenParserBroker(Twix_TokenParserBroker $broker)
    {
        $this->brokers[] = $broker;
    }
    
    public function removeTokenParserBroker(Twix_TokenParserBroker $broker)
    {
        if (false !== $pos = array_search($broker, $this->brokers)) {
            unset($this->brokers[$pos]);
        }
    }
    
    public function getTokenParser($tag)
    {
        if (isset($this->parsers[$tag])) {
            return $this->parsers[$tag];
        }
        $broker = end($this->brokers);
        while (false !== $broker) {
            $parser = $broker->getTokenParser($tag);
            if (null !== $parser) {
                return $parser;
            }
            $broker = prev($this->brokers);
        }
    }
    public function getParsers()
    {
        return $this->parsers;
    }
    public function getParser()
    {
        return $this->parser;
    }
    public function setParser(Twix_ParserInterface $parser)
    {
        $this->parser = $parser;
        foreach ($this->parsers as $tokenParser) {
            $tokenParser->setParser($parser);
        }
        foreach ($this->brokers as $broker) {
            $broker->setParser($parser);
        }
    }
}

class Twix_SimpleFilter
{
    protected $name;
    protected $callable;
    protected $options;
    protected $arguments = array();
    public function __construct($name, $callable, array $options = array())
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->options = array_merge(array(
            'needs_environment' => false,
            'needs_context' => false,
            'is_variadic' => false,
            'is_safe' => null,
            'is_safe_callback' => null,
            'pre_escape' => null,
            'preserves_safety' => null,
            'node_class' => 'Twix_Node_Expression_Filter',
            'deprecated' => false,
            'alternative' => null,
        ), $options);
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCallable()
    {
        return $this->callable;
    }
    public function getNodeClass()
    {
        return $this->options['node_class'];
    }
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function needsEnvironment()
    {
        return $this->options['needs_environment'];
    }
    public function needsContext()
    {
        return $this->options['needs_context'];
    }
    public function getSafe(Twix_Node $filterArgs)
    {
        if (null !== $this->options['is_safe']) {
            return $this->options['is_safe'];
        }
        if (null !== $this->options['is_safe_callback']) {
            return call_user_func($this->options['is_safe_callback'], $filterArgs);
        }
    }
    public function getPreservesSafety()
    {
        return $this->options['preserves_safety'];
    }
    public function getPreEscape()
    {
        return $this->options['pre_escape'];
    }
    public function isVariadic()
    {
        return $this->options['is_variadic'];
    }
    public function isDeprecated()
    {
        return (bool) $this->options['deprecated'];
    }
    public function getDeprecatedVersion()
    {
        return $this->options['deprecated'];
    }
    public function getAlternative()
    {
        return $this->options['alternative'];
    }
}

class Twix_SimpleFunction
{
    protected $name;
    protected $callable;
    protected $options;
    protected $arguments = array();
    public function __construct($name, $callable, array $options = array())
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->options = array_merge(array(
            'needs_environment' => false,
            'needs_context' => false,
            'is_variadic' => false,
            'is_safe' => null,
            'is_safe_callback' => null,
            'node_class' => 'Twix_Node_Expression_Function',
            'deprecated' => false,
            'alternative' => null,
        ), $options);
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCallable()
    {
        return $this->callable;
    }
    public function getNodeClass()
    {
        return $this->options['node_class'];
    }
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function needsEnvironment()
    {
        return $this->options['needs_environment'];
    }
    public function needsContext()
    {
        return $this->options['needs_context'];
    }
    public function getSafe(Twix_Node $functionArgs)
    {
        if (null !== $this->options['is_safe']) {
            return $this->options['is_safe'];
        }
        if (null !== $this->options['is_safe_callback']) {
            return call_user_func($this->options['is_safe_callback'], $functionArgs);
        }
        return array();
    }
    public function isVariadic()
    {
        return $this->options['is_variadic'];
    }
    public function isDeprecated()
    {
        return (bool) $this->options['deprecated'];
    }
    public function getDeprecatedVersion()
    {
        return $this->options['deprecated'];
    }
    public function getAlternative()
    {
        return $this->options['alternative'];
    }
}

class Twix_SimpleTest
{
    protected $name;
    protected $callable;
    protected $options;
    public function __construct($name, $callable, array $options = array())
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->options = array_merge(array(
            'is_variadic' => false,
            'node_class' => 'Twix_Node_Expression_Test',
            'deprecated' => false,
            'alternative' => null,
        ), $options);
    }
    public function getName()
    {
        return $this->name;
    }
    public function getCallable()
    {
        return $this->callable;
    }
    public function getNodeClass()
    {
        return $this->options['node_class'];
    }
    public function isVariadic()
    {
        return $this->options['is_variadic'];
    }
    public function isDeprecated()
    {
        return (bool) $this->options['deprecated'];
    }
    public function getDeprecatedVersion()
    {
        return $this->options['deprecated'];
    }
    public function getAlternative()
    {
        return $this->options['alternative'];
    }
}

interface Twix_TokenParserInterface
{
    
    public function setParser(Twix_Parser $parser);
    
    public function parse(Twix_Token $token);
    
    public function getTag();
}

abstract class Twix_TokenParser implements Twix_TokenParserInterface
{
    
    protected $parser;
    
    public function setParser(Twix_Parser $parser)
    {
        $this->parser = $parser;
    }
}

class Twix_TokenParser_For extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $targets = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $stream->expect(Twix_Token::OPERATOR_TYPE, 'in');
        $seq = $this->parser->getExpressionParser()->parseExpression();
        $ifexpr = null;
        if ($stream->nextIf(Twix_Token::NAME_TYPE, 'if')) {
            $ifexpr = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideForFork'));
        if ($stream->next()->getValue() == 'else') {
            $stream->expect(Twix_Token::BLOCK_END_TYPE);
            $else = $this->parser->subparse(array($this, 'decideForEnd'), true);
        } else {
            $else = null;
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        if (count($targets) > 1) {
            $keyTarget = $targets->getNode(0);
            $keyTarget = new Twix_Node_Expression_AssignName($keyTarget->getAttribute('name'), $keyTarget->getLine());
            $valueTarget = $targets->getNode(1);
            $valueTarget = new Twix_Node_Expression_AssignName($valueTarget->getAttribute('name'), $valueTarget->getLine());
        } else {
            $keyTarget = new Twix_Node_Expression_AssignName('_key', $lineno);
            $valueTarget = $targets->getNode(0);
            $valueTarget = new Twix_Node_Expression_AssignName($valueTarget->getAttribute('name'), $valueTarget->getLine());
        }
        if ($ifexpr) {
            $this->checkLoopUsageCondition($stream, $ifexpr);
            $this->checkLoopUsageBody($stream, $body);
        }
        return new Twix_Node_For($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, $lineno, $this->getTag());
    }
    public function decideForFork(Twix_Token $token)
    {
        return $token->test(array('else', 'endfor'));
    }
    public function decideForEnd(Twix_Token $token)
    {
        return $token->test('endfor');
    }
    // the loop variable cannot be used in the condition
    protected function checkLoopUsageCondition(Twix_TokenStream $stream, Twix_NodeInterface $node)
    {
        if ($node instanceof Twix_Node_Expression_GetAttr && $node->getNode('node') instanceof Twix_Node_Expression_Name && 'loop' == $node->getNode('node')->getAttribute('name')) {
            throw new Twix_Error_Syntax('The "loop" variable cannot be used in a looping condition.', $node->getLine(), $stream->getFilename());
        }
        foreach ($node as $n) {
            if (!$n) {
                continue;
            }
            $this->checkLoopUsageCondition($stream, $n);
        }
    }
    // check usage of non-defined loop-items
    // it does not catch all problems (for instance when a for is included into another or when the variable is used in an include)
    protected function checkLoopUsageBody(Twix_TokenStream $stream, Twix_NodeInterface $node)
    {
        if ($node instanceof Twix_Node_Expression_GetAttr && $node->getNode('node') instanceof Twix_Node_Expression_Name && 'loop' == $node->getNode('node')->getAttribute('name')) {
            $attribute = $node->getNode('attribute');
            if ($attribute instanceof Twix_Node_Expression_Constant && in_array($attribute->getAttribute('value'), array('length', 'revindex0', 'revindex', 'last'))) {
                throw new Twix_Error_Syntax(sprintf('The "loop.%s" variable is not defined when looping with a condition.', $attribute->getAttribute('value')), $node->getLine(), $stream->getFilename());
            }
        }
        // should check for parent.loop.XXX usage
        if ($node instanceof Twix_Node_For) {
            return;
        }
        foreach ($node as $n) {
            if (!$n) {
                continue;
            }
            $this->checkLoopUsageBody($stream, $n);
        }
    }
    public function getTag()
    {
        return 'for';
    }
}

class Twix_TokenParser_If extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideIfFork'));
        $tests = array($expr, $body);
        $else = null;
        $end = false;
        while (!$end) {
            switch ($stream->next()->getValue()) {
                case 'else':
                    $stream->expect(Twix_Token::BLOCK_END_TYPE);
                    $else = $this->parser->subparse(array($this, 'decideIfEnd'));
                    break;
                case 'elseif':
                    $expr = $this->parser->getExpressionParser()->parseExpression();
                    $stream->expect(Twix_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideIfFork'));
                    $tests[] = $expr;
                    $tests[] = $body;
                    break;
                case 'endif':
                    $end = true;
                    break;
                default:
                    throw new Twix_Error_Syntax(sprintf('Unexpected end of template. Twig was looking for the following tags "else", "elseif", or "endif" to close the "if" block started at line %d).', $lineno), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_If(new Twix_Node($tests), $else, $lineno, $this->getTag());
    }
    public function decideIfFork(Twix_Token $token)
    {
        return $token->test(array('elseif', 'else', 'endif'));
    }
    public function decideIfEnd(Twix_Token $token)
    {
        return $token->test(array('endif'));
    }
    public function getTag()
    {
        return 'if';
    }
}

class Twix_TokenParser_Extends extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        if (!$this->parser->isMainScope()) {
            throw new Twix_Error_Syntax('Cannot extend from a block.', $token->getLine(), $this->parser->getFilename());
        }
        if (null !== $this->parser->getParent()) {
            throw new Twix_Error_Syntax('Multiple extends tags are forbidden.', $token->getLine(), $this->parser->getFilename());
        }
        $this->parser->setParent($this->parser->getExpressionParser()->parseExpression());
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
    }
    public function getTag()
    {
        return 'extends';
    }
}

class Twix_TokenParser_Include extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        list($variables, $only, $ignoreMissing) = $this->parseArguments();
        return new Twix_Node_Include($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    protected function parseArguments()
    {
        $stream = $this->parser->getStream();
        $ignoreMissing = false;
        if ($stream->nextIf(Twix_Token::NAME_TYPE, 'ignore')) {
            $stream->expect(Twix_Token::NAME_TYPE, 'missing');
            $ignoreMissing = true;
        }
        $variables = null;
        if ($stream->nextIf(Twix_Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }
        $only = false;
        if ($stream->nextIf(Twix_Token::NAME_TYPE, 'only')) {
            $only = true;
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        return array($variables, $only, $ignoreMissing);
    }
    public function getTag()
    {
        return 'include';
    }
}

class Twix_TokenParser_Block extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
        if ($this->parser->hasBlock($name)) {
            throw new Twix_Error_Syntax(sprintf("The block '%s' has already been defined line %d.", $name, $this->parser->getBlock($name)->getLine()), $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $this->parser->setBlock($name, $block = new Twix_Node_Block($name, new Twix_Node(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);
        if ($stream->nextIf(Twix_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(Twix_Token::NAME_TYPE)) {
                $value = $token->getValue();
                if ($value != $name) {
                    throw new Twix_Error_Syntax(sprintf('Expected endblock for block "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }
            }
        } else {
            $body = new Twix_Node(array(
                new Twix_Node_Print($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();
        return new Twix_Node_BlockReference($name, $lineno, $this->getTag());
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endblock');
    }
    public function getTag()
    {
        return 'block';
    }
}

class Twix_TokenParser_Use extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $template = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        if (!$template instanceof Twix_Node_Expression_Constant) {
            throw new Twix_Error_Syntax('The template references in a "use" statement must be a string.', $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $targets = array();
        if ($stream->nextIf('with')) {
            do {
                $name = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
                $alias = $name;
                if ($stream->nextIf('as')) {
                    $alias = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
                }
                $targets[$name] = new Twix_Node_Expression_Constant($alias, -1);
                if (!$stream->nextIf(Twix_Token::PUNCTUATION_TYPE, ',')) {
                    break;
                }
            } while (true);
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $this->parser->addTrait(new Twix_Node(array('template' => $template, 'targets' => new Twix_Node($targets))));
    }
    public function getTag()
    {
        return 'use';
    }
}

class Twix_TokenParser_Filter extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $name = $this->parser->getVarName();
        $ref = new Twix_Node_Expression_BlockReference(new Twix_Node_Expression_Constant($name, $token->getLine()), true, $token->getLine(), $this->getTag());
        $filter = $this->parser->getExpressionParser()->parseFilterExpressionRaw($ref, $this->getTag());
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        $block = new Twix_Node_Block($name, $body, $token->getLine());
        $this->parser->setBlock($name, $block);
        return new Twix_Node_Print($filter, $token->getLine(), $this->getTag());
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endfilter');
    }
    public function getTag()
    {
        return 'filter';
    }
}

class Twix_TokenParser_Macro extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
        $arguments = $this->parser->getExpressionParser()->parseArguments(true, true);
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $this->parser->pushLocalScope();
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        if ($token = $stream->nextIf(Twix_Token::NAME_TYPE)) {
            $value = $token->getValue();
            if ($value != $name) {
                throw new Twix_Error_Syntax(sprintf('Expected endmacro for macro "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }
        $this->parser->popLocalScope();
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $this->parser->setMacro($name, new Twix_Node_Macro($name, new Twix_Node_Body(array($body)), $arguments, $lineno, $this->getTag()));
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endmacro');
    }
    public function getTag()
    {
        return 'macro';
    }
}

class Twix_TokenParser_Import extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $var = new Twix_Node_Expression_AssignName($this->parser->getStream()->expect(Twix_Token::NAME_TYPE)->getValue(), $token->getLine());
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        $this->parser->addImportedSymbol('template', $var->getAttribute('name'));
        return new Twix_Node_Import($macro, $var, $token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'import';
    }
}

class Twix_TokenParser_From extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect('import');
        $targets = array();
        do {
            $name = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
            $alias = $name;
            if ($stream->nextIf('as')) {
                $alias = $stream->expect(Twix_Token::NAME_TYPE)->getValue();
            }
            $targets[$name] = $alias;
            if (!$stream->nextIf(Twix_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        } while (true);
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $node = new Twix_Node_Import($macro, new Twix_Node_Expression_AssignName($this->parser->getVarName(), $token->getLine()), $token->getLine(), $this->getTag());
        foreach ($targets as $name => $alias) {
            if ($this->parser->isReservedMacroName($name)) {
                throw new Twix_Error_Syntax(sprintf('"%s" cannot be an imported macro as it is a reserved keyword.', $name), $token->getLine(), $stream->getFilename());
            }
            $this->parser->addImportedSymbol('function', $alias, $name, $node->getNode('var'));
        }
        return $node;
    }
    public function getTag()
    {
        return 'from';
    }
}

class Twix_TokenParser_Set extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $names = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $capture = false;
        if ($stream->nextIf(Twix_Token::OPERATOR_TYPE, '=')) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();
            $stream->expect(Twix_Token::BLOCK_END_TYPE);
            if (count($names) !== count($values)) {
                throw new Twix_Error_Syntax('When using set, you must have the same number of variables and assignments.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        } else {
            $capture = true;
            if (count($names) > 1) {
                throw new Twix_Error_Syntax('When using set with a block, you cannot have a multi-target.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
            $stream->expect(Twix_Token::BLOCK_END_TYPE);
            $values = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            $stream->expect(Twix_Token::BLOCK_END_TYPE);
        }
        return new Twix_Node_Set($capture, $names, $values, $lineno, $this->getTag());
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endset');
    }
    public function getTag()
    {
        return 'set';
    }
}

class Twix_TokenParser_Spaceless extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideSpacelessEnd'), true);
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_Spaceless($body, $lineno, $this->getTag());
    }
    public function decideSpacelessEnd(Twix_Token $token)
    {
        return $token->test('endspaceless');
    }
    public function getTag()
    {
        return 'spaceless';
    }
}

class Twix_TokenParser_Flush extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_Flush($token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'flush';
    }
}

class Twix_TokenParser_Do extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_Do($expr, $token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'do';
    }
}

class Twix_TokenParser_Embed extends Twix_TokenParser_Include
{
    public function parse(Twix_Token $token)
    {
        $stream = $this->parser->getStream();
        $parent = $this->parser->getExpressionParser()->parseExpression();
        list($variables, $only, $ignoreMissing) = $this->parseArguments();
        // inject a fake parent to make the parent() function work
        $stream->injectTokens(array(
            new Twix_Token(Twix_Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Twix_Token(Twix_Token::NAME_TYPE, 'extends', $token->getLine()),
            new Twix_Token(Twix_Token::STRING_TYPE, '__parent__', $token->getLine()),
            new Twix_Token(Twix_Token::BLOCK_END_TYPE, '', $token->getLine()),
        ));
        $module = $this->parser->parse($stream, array($this, 'decideBlockEnd'), true);
        // override the parent with the correct one
        $module->setNode('parent', $parent);
        $this->parser->embedTemplate($module);
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_Embed($module->getAttribute('filename'), $module->getAttribute('index'), $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endembed');
    }
    public function getTag()
    {
        return 'embed';
    }
}

class Twix_ExpressionParser
{
    const OPERATOR_LEFT = 1;
    const OPERATOR_RIGHT = 2;
    protected $parser;
    protected $unaryOperators;
    protected $binaryOperators;
    public function __construct(Twix_Parser $parser, array $unaryOperators, array $binaryOperators)
    {
        $this->parser = $parser;
        $this->unaryOperators = $unaryOperators;
        $this->binaryOperators = $binaryOperators;
    }
    public function parseExpression($precedence = 0)
    {
        $expr = $this->getPrimary();
        $token = $this->parser->getCurrentToken();
        while ($this->isBinary($token) && $this->binaryOperators[$token->getValue()]['precedence'] >= $precedence) {
            $op = $this->binaryOperators[$token->getValue()];
            $this->parser->getStream()->next();
            if (isset($op['callable'])) {
                $expr = call_user_func($op['callable'], $this->parser, $expr);
            } else {
                $expr1 = $this->parseExpression(self::OPERATOR_LEFT === $op['associativity'] ? $op['precedence'] + 1 : $op['precedence']);
                $class = $op['class'];
                $expr = new $class($expr, $expr1, $token->getLine());
            }
            $token = $this->parser->getCurrentToken();
        }
        if (0 === $precedence) {
            return $this->parseConditionalExpression($expr);
        }
        return $expr;
    }
    protected function getPrimary()
    {
        $token = $this->parser->getCurrentToken();
        if ($this->isUnary($token)) {
            $operator = $this->unaryOperators[$token->getValue()];
            $this->parser->getStream()->next();
            $expr = $this->parseExpression($operator['precedence']);
            $class = $operator['class'];
            return $this->parsePostfixExpression(new $class($expr, $token->getLine()));
        } elseif ($token->test(Twix_Token::PUNCTUATION_TYPE, '(')) {
            $this->parser->getStream()->next();
            $expr = $this->parseExpression();
            $this->parser->getStream()->expect(Twix_Token::PUNCTUATION_TYPE, ')', 'An opened parenthesis is not properly closed');
            return $this->parsePostfixExpression($expr);
        }
        return $this->parsePrimaryExpression();
    }
    protected function parseConditionalExpression($expr)
    {
        while ($this->parser->getStream()->nextIf(Twix_Token::PUNCTUATION_TYPE, '?')) {
            if (!$this->parser->getStream()->nextIf(Twix_Token::PUNCTUATION_TYPE, ':')) {
                $expr2 = $this->parseExpression();
                if ($this->parser->getStream()->nextIf(Twix_Token::PUNCTUATION_TYPE, ':')) {
                    $expr3 = $this->parseExpression();
                } else {
                    $expr3 = new Twix_Node_Expression_Constant('', $this->parser->getCurrentToken()->getLine());
                }
            } else {
                $expr2 = $expr;
                $expr3 = $this->parseExpression();
            }
            $expr = new Twix_Node_Expression_Conditional($expr, $expr2, $expr3, $this->parser->getCurrentToken()->getLine());
        }
        return $expr;
    }
    protected function isUnary(Twix_Token $token)
    {
        return $token->test(Twix_Token::OPERATOR_TYPE) && isset($this->unaryOperators[$token->getValue()]);
    }
    protected function isBinary(Twix_Token $token)
    {
        return $token->test(Twix_Token::OPERATOR_TYPE) && isset($this->binaryOperators[$token->getValue()]);
    }
    public function parsePrimaryExpression()
    {
        $token = $this->parser->getCurrentToken();
        switch ($token->getType()) {
            case Twix_Token::NAME_TYPE:
                $this->parser->getStream()->next();
                switch ($token->getValue()) {
                    case 'true':
                    case 'TRUE':
                        $node = new Twix_Node_Expression_Constant(true, $token->getLine());
                        break;
                    case 'false':
                    case 'FALSE':
                        $node = new Twix_Node_Expression_Constant(false, $token->getLine());
                        break;
                    case 'none':
                    case 'NONE':
                    case 'null':
                    case 'NULL':
                        $node = new Twix_Node_Expression_Constant(null, $token->getLine());
                        break;
                    default:
                        if ('(' === $this->parser->getCurrentToken()->getValue()) {
                            $node = $this->getFunctionNode($token->getValue(), $token->getLine());
                        } else {
                            $node = new Twix_Node_Expression_Name($token->getValue(), $token->getLine());
                        }
                }
                break;
            case Twix_Token::NUMBER_TYPE:
                $this->parser->getStream()->next();
                $node = new Twix_Node_Expression_Constant($token->getValue(), $token->getLine());
                break;
            case Twix_Token::STRING_TYPE:
            case Twix_Token::INTERPOLATION_START_TYPE:
                $node = $this->parseStringExpression();
                break;
            case Twix_Token::OPERATOR_TYPE:
                if (preg_match(Twix_Lexer::REGEX_NAME, $token->getValue(), $matches) && $matches[0] == $token->getValue()) {
                    // in this context, string operators are variable names
                    $this->parser->getStream()->next();
                    $node = new Twix_Node_Expression_Name($token->getValue(), $token->getLine());
                    break;
                } elseif (isset($this->unaryOperators[$token->getValue()])) {
                    $class = $this->unaryOperators[$token->getValue()]['class'];
                    $ref = new ReflectionClass($class);
                    $negClass = 'Twix_Node_Expression_Unary_Neg';
                    $posClass = 'Twix_Node_Expression_Unary_Pos';
                    if (!(in_array($ref->getName(), array($negClass, $posClass)) || $ref->isSubclassOf($negClass) || $ref->isSubclassOf($posClass))) {
                        throw new Twix_Error_Syntax(sprintf('Unexpected unary operator "%s".', $token->getValue()), $token->getLine(), $this->parser->getFilename());
                    }
                    $this->parser->getStream()->next();
                    $expr = $this->parsePrimaryExpression();
                    $node = new $class($expr, $token->getLine());
                    break;
                }
            default:
                if ($token->test(Twix_Token::PUNCTUATION_TYPE, '[')) {
                    $node = $this->parseArrayExpression();
                } elseif ($token->test(Twix_Token::PUNCTUATION_TYPE, '{')) {
                    $node = $this->parseHashExpression();
                } else {
                    throw new Twix_Error_Syntax(sprintf('Unexpected token "%s" of value "%s".', Twix_Token::typeToEnglish($token->getType()), $token->getValue()), $token->getLine(), $this->parser->getFilename());
                }
        }
        return $this->parsePostfixExpression($node);
    }
    public function parseStringExpression()
    {
        $stream = $this->parser->getStream();
        $nodes = array();
        // a string cannot be followed by another string in a single expression
        $nextCanBeString = true;
        while (true) {
            if ($nextCanBeString && $token = $stream->nextIf(Twix_Token::STRING_TYPE)) {
                $nodes[] = new Twix_Node_Expression_Constant($token->getValue(), $token->getLine());
                $nextCanBeString = false;
            } elseif ($stream->nextIf(Twix_Token::INTERPOLATION_START_TYPE)) {
                $nodes[] = $this->parseExpression();
                $stream->expect(Twix_Token::INTERPOLATION_END_TYPE);
                $nextCanBeString = true;
            } else {
                break;
            }
        }
        $expr = array_shift($nodes);
        foreach ($nodes as $node) {
            $expr = new Twix_Node_Expression_Binary_Concat($expr, $node, $node->getLine());
        }
        return $expr;
    }
    public function parseArrayExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, '[', 'An array element was expected');
        $node = new Twix_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Twix_Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $stream->expect(Twix_Token::PUNCTUATION_TYPE, ',', 'An array element must be followed by a comma');
                // trailing ,?
                if ($stream->test(Twix_Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = false;
            $node->addElement($this->parseExpression());
        }
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');
        return $node;
    }
    public function parseHashExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, '{', 'A hash element was expected');
        $node = new Twix_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Twix_Token::PUNCTUATION_TYPE, '}')) {
            if (!$first) {
                $stream->expect(Twix_Token::PUNCTUATION_TYPE, ',', 'A hash value must be followed by a comma');
                // trailing ,?
                if ($stream->test(Twix_Token::PUNCTUATION_TYPE, '}')) {
                    break;
                }
            }
            $first = false;
            // a hash key can be:
            //
            //  * a number -- 12
            //  * a string -- 'a'
            //  * a name, which is equivalent to a string -- a
            //  * an expression, which must be enclosed in parentheses -- (1 + 2)
            if (($token = $stream->nextIf(Twix_Token::STRING_TYPE)) || ($token = $stream->nextIf(Twix_Token::NAME_TYPE)) || $token = $stream->nextIf(Twix_Token::NUMBER_TYPE)) {
                $key = new Twix_Node_Expression_Constant($token->getValue(), $token->getLine());
            } elseif ($stream->test(Twix_Token::PUNCTUATION_TYPE, '(')) {
                $key = $this->parseExpression();
            } else {
                $current = $stream->getCurrent();
                throw new Twix_Error_Syntax(sprintf('A hash key must be a quoted string, a number, a name, or an expression enclosed in parentheses (unexpected token "%s" of value "%s".', Twix_Token::typeToEnglish($current->getType()), $current->getValue()), $current->getLine(), $this->parser->getFilename());
            }
            $stream->expect(Twix_Token::PUNCTUATION_TYPE, ':', 'A hash key must be followed by a colon (:)');
            $value = $this->parseExpression();
            $node->addElement($value, $key);
        }
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, '}', 'An opened hash is not properly closed');
        return $node;
    }
    public function parsePostfixExpression($node)
    {
        while (true) {
            $token = $this->parser->getCurrentToken();
            if ($token->getType() == Twix_Token::PUNCTUATION_TYPE) {
                if ('.' == $token->getValue() || '[' == $token->getValue()) {
                    $node = $this->parseSubscriptExpression($node);
                } elseif ('|' == $token->getValue()) {
                    $node = $this->parseFilterExpression($node);
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        return $node;
    }
    public function getFunctionNode($name, $line)
    {
        switch ($name) {
            case 'parent':
                $this->parseArguments();
                if (!count($this->parser->getBlockStack())) {
                    throw new Twix_Error_Syntax('Calling "parent" outside a block is forbidden.', $line, $this->parser->getFilename());
                }
                if (!$this->parser->getParent() && !$this->parser->hasTraits()) {
                    throw new Twix_Error_Syntax('Calling "parent" on a template that does not extend nor "use" another template is forbidden.', $line, $this->parser->getFilename());
                }
                return new Twix_Node_Expression_Parent($this->parser->peekBlockStack(), $line);
            case 'block':
                return new Twix_Node_Expression_BlockReference($this->parseArguments()->getNode(0), false, $line);
            case 'attribute':
                $args = $this->parseArguments();
                if (count($args) < 2) {
                    throw new Twix_Error_Syntax('The "attribute" function takes at least two arguments (the variable and the attributes).', $line, $this->parser->getFilename());
                }
                return new Twix_Node_Expression_GetAttr($args->getNode(0), $args->getNode(1), count($args) > 2 ? $args->getNode(2) : null, Twix_Template::ANY_CALL, $line);
            default:
                if (null !== $alias = $this->parser->getImportedSymbol('function', $name)) {
                    $arguments = new Twix_Node_Expression_Array(array(), $line);
                    foreach ($this->parseArguments() as $n) {
                        $arguments->addElement($n);
                    }
                    $node = new Twix_Node_Expression_MethodCall($alias['node'], $alias['name'], $arguments, $line);
                    $node->setAttribute('safe', true);
                    return $node;
                }
                $args = $this->parseArguments(true);
                $class = $this->getFunctionNodeClass($name, $line);
                return new $class($name, $args, $line);
        }
    }
    public function parseSubscriptExpression($node)
    {
        $stream = $this->parser->getStream();
        $token = $stream->next();
        $lineno = $token->getLine();
        $arguments = new Twix_Node_Expression_Array(array(), $lineno);
        $type = Twix_Template::ANY_CALL;
        if ($token->getValue() == '.') {
            $token = $stream->next();
            if (
                $token->getType() == Twix_Token::NAME_TYPE
                ||
                $token->getType() == Twix_Token::NUMBER_TYPE
                ||
                ($token->getType() == Twix_Token::OPERATOR_TYPE && preg_match(Twix_Lexer::REGEX_NAME, $token->getValue()))
            ) {
                $arg = new Twix_Node_Expression_Constant($token->getValue(), $lineno);
                if ($stream->test(Twix_Token::PUNCTUATION_TYPE, '(')) {
                    $type = Twix_Template::METHOD_CALL;
                    foreach ($this->parseArguments() as $n) {
                        $arguments->addElement($n);
                    }
                }
            } else {
                throw new Twix_Error_Syntax('Expected name or number', $lineno, $this->parser->getFilename());
            }
            if ($node instanceof Twix_Node_Expression_Name && null !== $this->parser->getImportedSymbol('template', $node->getAttribute('name'))) {
                if (!$arg instanceof Twix_Node_Expression_Constant) {
                    throw new Twix_Error_Syntax(sprintf('Dynamic macro names are not supported (called on "%s").', $node->getAttribute('name')), $token->getLine(), $this->parser->getFilename());
                }
                $name = $arg->getAttribute('value');
                if ($this->parser->isReservedMacroName($name)) {
                    throw new Twix_Error_Syntax(sprintf('"%s" cannot be called as macro as it is a reserved keyword.', $name), $token->getLine(), $this->parser->getFilename());
                }
                $node = new Twix_Node_Expression_MethodCall($node, $name, $arguments, $lineno);
                $node->setAttribute('safe', true);
                return $node;
            }
        } else {
            $type = Twix_Template::ARRAY_CALL;
            // slice?
            $slice = false;
            if ($stream->test(Twix_Token::PUNCTUATION_TYPE, ':')) {
                $slice = true;
                $arg = new Twix_Node_Expression_Constant(0, $token->getLine());
            } else {
                $arg = $this->parseExpression();
            }
            if ($stream->nextIf(Twix_Token::PUNCTUATION_TYPE, ':')) {
                $slice = true;
            }
            if ($slice) {
                if ($stream->test(Twix_Token::PUNCTUATION_TYPE, ']')) {
                    $length = new Twix_Node_Expression_Constant(null, $token->getLine());
                } else {
                    $length = $this->parseExpression();
                }
                $class = $this->getFilterNodeClass('slice', $token->getLine());
                $arguments = new Twix_Node(array($arg, $length));
                $filter = new $class($node, new Twix_Node_Expression_Constant('slice', $token->getLine()), $arguments, $token->getLine());
                $stream->expect(Twix_Token::PUNCTUATION_TYPE, ']');
                return $filter;
            }
            $stream->expect(Twix_Token::PUNCTUATION_TYPE, ']');
        }
        return new Twix_Node_Expression_GetAttr($node, $arg, $arguments, $type, $lineno);
    }
    public function parseFilterExpression($node)
    {
        $this->parser->getStream()->next();
        return $this->parseFilterExpressionRaw($node);
    }
    public function parseFilterExpressionRaw($node, $tag = null)
    {
        while (true) {
            $token = $this->parser->getStream()->expect(Twix_Token::NAME_TYPE);
            $name = new Twix_Node_Expression_Constant($token->getValue(), $token->getLine());
            if (!$this->parser->getStream()->test(Twix_Token::PUNCTUATION_TYPE, '(')) {
                $arguments = new Twix_Node();
            } else {
                $arguments = $this->parseArguments(true);
            }
            $class = $this->getFilterNodeClass($name->getAttribute('value'), $token->getLine());
            $node = new $class($node, $name, $arguments, $token->getLine(), $tag);
            if (!$this->parser->getStream()->test(Twix_Token::PUNCTUATION_TYPE, '|')) {
                break;
            }
            $this->parser->getStream()->next();
        }
        return $node;
    }
    
    public function parseArguments($namedArguments = false, $definition = false)
    {
        $args = array();
        $stream = $this->parser->getStream();
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, '(', 'A list of arguments must begin with an opening parenthesis');
        while (!$stream->test(Twix_Token::PUNCTUATION_TYPE, ')')) {
            if (!empty($args)) {
                $stream->expect(Twix_Token::PUNCTUATION_TYPE, ',', 'Arguments must be separated by a comma');
            }
            if ($definition) {
                $token = $stream->expect(Twix_Token::NAME_TYPE, null, 'An argument must be a name');
                $value = new Twix_Node_Expression_Name($token->getValue(), $this->parser->getCurrentToken()->getLine());
            } else {
                $value = $this->parseExpression();
            }
            $name = null;
            if ($namedArguments && $token = $stream->nextIf(Twix_Token::OPERATOR_TYPE, '=')) {
                if (!$value instanceof Twix_Node_Expression_Name) {
                    throw new Twix_Error_Syntax(sprintf('A parameter name must be a string, "%s" given.', get_class($value)), $token->getLine(), $this->parser->getFilename());
                }
                $name = $value->getAttribute('name');
                if ($definition) {
                    $value = $this->parsePrimaryExpression();
                    if (!$this->checkConstantExpression($value)) {
                        throw new Twix_Error_Syntax(sprintf('A default value for an argument must be a constant (a boolean, a string, a number, or an array).'), $token->getLine(), $this->parser->getFilename());
                    }
                } else {
                    $value = $this->parseExpression();
                }
            }
            if ($definition) {
                if (null === $name) {
                    $name = $value->getAttribute('name');
                    $value = new Twix_Node_Expression_Constant(null, $this->parser->getCurrentToken()->getLine());
                }
                $args[$name] = $value;
            } else {
                if (null === $name) {
                    $args[] = $value;
                } else {
                    $args[$name] = $value;
                }
            }
        }
        $stream->expect(Twix_Token::PUNCTUATION_TYPE, ')', 'A list of arguments must be closed by a parenthesis');
        return new Twix_Node($args);
    }
    public function parseAssignmentExpression()
    {
        $targets = array();
        while (true) {
            $token = $this->parser->getStream()->expect(Twix_Token::NAME_TYPE, null, 'Only variables can be assigned to');
            $value = $token->getValue();
            if (in_array(strtolower($value), array('true', 'false', 'none', 'null'))) {
                throw new Twix_Error_Syntax(sprintf('You cannot assign a value to "%s"', $value), $token->getLine(), $this->parser->getFilename());
            }
            $targets[] = new Twix_Node_Expression_AssignName($value, $token->getLine());
            if (!$this->parser->getStream()->nextIf(Twix_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }
        return new Twix_Node($targets);
    }
    public function parseMultitargetExpression()
    {
        $targets = array();
        while (true) {
            $targets[] = $this->parseExpression();
            if (!$this->parser->getStream()->nextIf(Twix_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }
        return new Twix_Node($targets);
    }
    protected function getFunctionNodeClass($name, $line)
    {
        $env = $this->parser->getEnvironment();
        if (false === $function = $env->getFunction($name)) {
            $e = new Twix_Error_Syntax(sprintf('Unknown "%s" function.', $name), $line, $this->parser->getFilename());
            $e->addSuggestions($name, array_keys($env->getFunctions()));
            throw $e;
        }
        if ($function instanceof Twix_SimpleFunction && $function->isDeprecated()) {
            $message = sprintf('Twig Function "%s" is deprecated', $function->getName());
            if (!is_bool($function->getDeprecatedVersion())) {
                $message .= sprintf(' since version %s', $function->getDeprecatedVersion());
            }
            if ($function->getAlternative()) {
                $message .= sprintf('. Use "%s" instead', $function->getAlternative());
            }
            $message .= sprintf(' in %s at line %d.', $this->parser->getFilename(), $line);
            @trigger_error($message, E_USER_DEPRECATED);
        }
        if ($function instanceof Twix_SimpleFunction) {
            return $function->getNodeClass();
        }
        return $function instanceof Twix_Function_Node ? $function->getClass() : 'Twix_Node_Expression_Function';
    }
    protected function getFilterNodeClass($name, $line)
    {
        $env = $this->parser->getEnvironment();
        if (false === $filter = $env->getFilter($name)) {
            $e = new Twix_Error_Syntax(sprintf('Unknown "%s" filter.', $name), $line, $this->parser->getFilename());
            $e->addSuggestions($name, array_keys($env->getFilters()));
            throw $e;
        }
        if ($filter instanceof Twix_SimpleFilter && $filter->isDeprecated()) {
            $message = sprintf('Twig Filter "%s" is deprecated', $filter->getName());
            if (!is_bool($filter->getDeprecatedVersion())) {
                $message .= sprintf(' since version %s', $filter->getDeprecatedVersion());
            }
            if ($filter->getAlternative()) {
                $message .= sprintf('. Use "%s" instead', $filter->getAlternative());
            }
            $message .= sprintf(' in %s at line %d.', $this->parser->getFilename(), $line);
            @trigger_error($message, E_USER_DEPRECATED);
        }
        if ($filter instanceof Twix_SimpleFilter) {
            return $filter->getNodeClass();
        }
        return $filter instanceof Twix_Filter_Node ? $filter->getClass() : 'Twix_Node_Expression_Filter';
    }
    // checks that the node only contains "constant" elements
    protected function checkConstantExpression(Twix_NodeInterface $node)
    {
        if (!($node instanceof Twix_Node_Expression_Constant || $node instanceof Twix_Node_Expression_Array
            || $node instanceof Twix_Node_Expression_Unary_Neg || $node instanceof Twix_Node_Expression_Unary_Pos
        )) {
            return false;
        }
        foreach ($node as $n) {
            if (!$this->checkConstantExpression($n)) {
                return false;
            }
        }
        return true;
    }
}

class Twix_TokenParser_AutoEscape extends Twix_TokenParser
{
    public function parse(Twix_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        if ($stream->test(Twix_Token::BLOCK_END_TYPE)) {
            $value = 'html';
        } else {
            $expr = $this->parser->getExpressionParser()->parseExpression();
            if (!$expr instanceof Twix_Node_Expression_Constant) {
                throw new Twix_Error_Syntax('An escaping strategy must be a string or a bool.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
            $value = $expr->getAttribute('value');
            $compat = true === $value || false === $value;
            if (true === $value) {
                $value = 'html';
            }
            if ($compat && $stream->test(Twix_Token::NAME_TYPE)) {
                @trigger_error('Using the autoescape tag with "true" or "false" before the strategy name is deprecated since version 1.21.', E_USER_DEPRECATED);
                if (false === $value) {
                    throw new Twix_Error_Syntax('Unexpected escaping strategy as you set autoescaping to false.', $stream->getCurrent()->getLine(), $stream->getFilename());
                }
                $value = $stream->next()->getValue();
            }
        }
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $stream->expect(Twix_Token::BLOCK_END_TYPE);
        return new Twix_Node_AutoEscape($value, $body, $lineno, $this->getTag());
    }
    public function decideBlockEnd(Twix_Token $token)
    {
        return $token->test('endautoescape');
    }
    public function getTag()
    {
        return 'autoescape';
    }
}

interface Twix_NodeVisitorInterface
{
    
    public function enterNode(Twix_NodeInterface $node, Twix_Environment $env);
    
    public function leaveNode(Twix_NodeInterface $node, Twix_Environment $env);
    
    public function getPriority();
}

abstract class Twix_BaseNodeVisitor implements Twix_NodeVisitorInterface
{
    
    final public function enterNode(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if (!$node instanceof Twix_Node) {
            throw new LogicException('Twix_BaseNodeVisitor only supports Twix_Node instances.');
        }
        return $this->doEnterNode($node, $env);
    }
    
    final public function leaveNode(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if (!$node instanceof Twix_Node) {
            throw new LogicException('Twix_BaseNodeVisitor only supports Twix_Node instances.');
        }
        return $this->doLeaveNode($node, $env);
    }
    
    abstract protected function doEnterNode(Twix_Node $node, Twix_Environment $env);
    
    abstract protected function doLeaveNode(Twix_Node $node, Twix_Environment $env);
}

class Twix_NodeVisitor_Escaper extends Twix_BaseNodeVisitor
{
    protected $statusStack = array();
    protected $blocks = array();
    protected $safeAnalysis;
    protected $traverser;
    protected $defaultStrategy = false;
    protected $safeVars = array();
    public function __construct()
    {
        $this->safeAnalysis = new Twix_NodeVisitor_SafeAnalysis();
    }
    
    protected function doEnterNode(Twix_Node $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_Module) {
            if ($env->hasExtension('escaper') && $defaultStrategy = $env->getExtension('escaper')->getDefaultStrategy($node->getAttribute('filename'))) {
                $this->defaultStrategy = $defaultStrategy;
            }
            $this->safeVars = array();
            $this->blocks = array();
        } elseif ($node instanceof Twix_Node_AutoEscape) {
            $this->statusStack[] = $node->getAttribute('value');
        } elseif ($node instanceof Twix_Node_Block) {
            $this->statusStack[] = isset($this->blocks[$node->getAttribute('name')]) ? $this->blocks[$node->getAttribute('name')] : $this->needEscaping($env);
        } elseif ($node instanceof Twix_Node_Import) {
            $this->safeVars[] = $node->getNode('var')->getAttribute('name');
        }
        return $node;
    }
    
    protected function doLeaveNode(Twix_Node $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_Module) {
            $this->defaultStrategy = false;
            $this->safeVars = array();
            $this->blocks = array();
        } elseif ($node instanceof Twix_Node_Expression_Filter) {
            return $this->preEscapeFilterNode($node, $env);
        } elseif ($node instanceof Twix_Node_Print) {
            return $this->escapePrintNode($node, $env, $this->needEscaping($env));
        }
        if ($node instanceof Twix_Node_AutoEscape || $node instanceof Twix_Node_Block) {
            array_pop($this->statusStack);
        } elseif ($node instanceof Twix_Node_BlockReference) {
            $this->blocks[$node->getAttribute('name')] = $this->needEscaping($env);
        }
        return $node;
    }
    protected function escapePrintNode(Twix_Node_Print $node, Twix_Environment $env, $type)
    {
        if (false === $type) {
            return $node;
        }
        $expression = $node->getNode('expr');
        if ($this->isSafeFor($type, $expression, $env)) {
            return $node;
        }
        $class = get_class($node);
        return new $class(
            $this->getEscaperFilter($type, $expression),
            $node->getLine()
        );
    }
    protected function preEscapeFilterNode(Twix_Node_Expression_Filter $filter, Twix_Environment $env)
    {
        $name = $filter->getNode('filter')->getAttribute('value');
        $type = $env->getFilter($name)->getPreEscape();
        if (null === $type) {
            return $filter;
        }
        $node = $filter->getNode('node');
        if ($this->isSafeFor($type, $node, $env)) {
            return $filter;
        }
        $filter->setNode('node', $this->getEscaperFilter($type, $node));
        return $filter;
    }
    protected function isSafeFor($type, Twix_NodeInterface $expression, $env)
    {
        $safe = $this->safeAnalysis->getSafe($expression);
        if (null === $safe) {
            if (null === $this->traverser) {
                $this->traverser = new Twix_NodeTraverser($env, array($this->safeAnalysis));
            }
            $this->safeAnalysis->setSafeVars($this->safeVars);
            $this->traverser->traverse($expression);
            $safe = $this->safeAnalysis->getSafe($expression);
        }
        return in_array($type, $safe) || in_array('all', $safe);
    }
    protected function needEscaping(Twix_Environment $env)
    {
        if (count($this->statusStack)) {
            return $this->statusStack[count($this->statusStack) - 1];
        }
        return $this->defaultStrategy ? $this->defaultStrategy : false;
    }
    protected function getEscaperFilter($type, Twix_NodeInterface $node)
    {
        $line = $node->getLine();
        $name = new Twix_Node_Expression_Constant('escape', $line);
        $args = new Twix_Node(array(new Twix_Node_Expression_Constant((string) $type, $line), new Twix_Node_Expression_Constant(null, $line), new Twix_Node_Expression_Constant(true, $line)));
        return new Twix_Node_Expression_Filter($node, $name, $args, $line);
    }
    
    public function getPriority()
    {
        return 0;
    }
}

class Twix_NodeVisitor_SafeAnalysis extends Twix_BaseNodeVisitor
{
    protected $data = array();
    protected $safeVars = array();
    public function setSafeVars($safeVars)
    {
        $this->safeVars = $safeVars;
    }
    public function getSafe(Twix_NodeInterface $node)
    {
        $hash = spl_object_hash($node);
        if (!isset($this->data[$hash])) {
            return;
        }
        foreach ($this->data[$hash] as $bucket) {
            if ($bucket['key'] !== $node) {
                continue;
            }
            if (in_array('html_attr', $bucket['value'])) {
                $bucket['value'][] = 'html';
            }
            return $bucket['value'];
        }
    }
    protected function setSafe(Twix_NodeInterface $node, array $safe)
    {
        $hash = spl_object_hash($node);
        if (isset($this->data[$hash])) {
            foreach ($this->data[$hash] as &$bucket) {
                if ($bucket['key'] === $node) {
                    $bucket['value'] = $safe;
                    return;
                }
            }
        }
        $this->data[$hash][] = array(
            'key' => $node,
            'value' => $safe,
        );
    }
    
    protected function doEnterNode(Twix_Node $node, Twix_Environment $env)
    {
        return $node;
    }
    
    protected function doLeaveNode(Twix_Node $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_Expression_Constant) {
            // constants are marked safe for all
            $this->setSafe($node, array('all'));
        } elseif ($node instanceof Twix_Node_Expression_BlockReference) {
            // blocks are safe by definition
            $this->setSafe($node, array('all'));
        } elseif ($node instanceof Twix_Node_Expression_Parent) {
            // parent block is safe by definition
            $this->setSafe($node, array('all'));
        } elseif ($node instanceof Twix_Node_Expression_Conditional) {
            // intersect safeness of both operands
            $safe = $this->intersectSafe($this->getSafe($node->getNode('expr2')), $this->getSafe($node->getNode('expr3')));
            $this->setSafe($node, $safe);
        } elseif ($node instanceof Twix_Node_Expression_Filter) {
            // filter expression is safe when the filter is safe
            $name = $node->getNode('filter')->getAttribute('value');
            $args = $node->getNode('arguments');
            if (false !== $filter = $env->getFilter($name)) {
                $safe = $filter->getSafe($args);
                if (null === $safe) {
                    $safe = $this->intersectSafe($this->getSafe($node->getNode('node')), $filter->getPreservesSafety());
                }
                $this->setSafe($node, $safe);
            } else {
                $this->setSafe($node, array());
            }
        } elseif ($node instanceof Twix_Node_Expression_Function) {
            // function expression is safe when the function is safe
            $name = $node->getAttribute('name');
            $args = $node->getNode('arguments');
            $function = $env->getFunction($name);
            if (false !== $function) {
                $this->setSafe($node, $function->getSafe($args));
            } else {
                $this->setSafe($node, array());
            }
        } elseif ($node instanceof Twix_Node_Expression_MethodCall) {
            if ($node->getAttribute('safe')) {
                $this->setSafe($node, array('all'));
            } else {
                $this->setSafe($node, array());
            }
        } elseif ($node instanceof Twix_Node_Expression_GetAttr && $node->getNode('node') instanceof Twix_Node_Expression_Name) {
            $name = $node->getNode('node')->getAttribute('name');
            // attributes on template instances are safe
            if ('_self' == $name || in_array($name, $this->safeVars)) {
                $this->setSafe($node, array('all'));
            } else {
                $this->setSafe($node, array());
            }
        } else {
            $this->setSafe($node, array());
        }
        return $node;
    }
    protected function intersectSafe(array $a = null, array $b = null)
    {
        if (null === $a || null === $b) {
            return array();
        }
        if (in_array('all', $a)) {
            return $b;
        }
        if (in_array('all', $b)) {
            return $a;
        }
        return array_intersect($a, $b);
    }
    
    public function getPriority()
    {
        return 0;
    }
}

class Twix_NodeVisitor_Optimizer extends Twix_BaseNodeVisitor
{
    const OPTIMIZE_ALL = -1;
    const OPTIMIZE_NONE = 0;
    const OPTIMIZE_FOR = 2;
    const OPTIMIZE_RAW_FILTER = 4;
    const OPTIMIZE_VAR_ACCESS = 8;
    protected $loops = array();
    protected $loopsTargets = array();
    protected $optimizers;
    protected $prependedNodes = array();
    protected $inABody = false;
    
    public function __construct($optimizers = -1)
    {
        if (!is_int($optimizers) || $optimizers > (self::OPTIMIZE_FOR | self::OPTIMIZE_RAW_FILTER | self::OPTIMIZE_VAR_ACCESS)) {
            throw new InvalidArgumentException(sprintf('Optimizer mode "%s" is not valid.', $optimizers));
        }
        $this->optimizers = $optimizers;
    }
    
    protected function doEnterNode(Twix_Node $node, Twix_Environment $env)
    {
        if (self::OPTIMIZE_FOR === (self::OPTIMIZE_FOR & $this->optimizers)) {
            $this->enterOptimizeFor($node, $env);
        }
        if (PHP_VERSION_ID < 50400 && self::OPTIMIZE_VAR_ACCESS === (self::OPTIMIZE_VAR_ACCESS & $this->optimizers) && !$env->isStrictVariables() && !$env->hasExtension('sandbox')) {
            if ($this->inABody) {
                if (!$node instanceof Twix_Node_Expression) {
                    if (get_class($node) !== 'Twix_Node') {
                        array_unshift($this->prependedNodes, array());
                    }
                } else {
                    $node = $this->optimizeVariables($node, $env);
                }
            } elseif ($node instanceof Twix_Node_Body) {
                $this->inABody = true;
            }
        }
        return $node;
    }
    
    protected function doLeaveNode(Twix_Node $node, Twix_Environment $env)
    {
        $expression = $node instanceof Twix_Node_Expression;
        if (self::OPTIMIZE_FOR === (self::OPTIMIZE_FOR & $this->optimizers)) {
            $this->leaveOptimizeFor($node, $env);
        }
        if (self::OPTIMIZE_RAW_FILTER === (self::OPTIMIZE_RAW_FILTER & $this->optimizers)) {
            $node = $this->optimizeRawFilter($node, $env);
        }
        $node = $this->optimizePrintNode($node, $env);
        if (self::OPTIMIZE_VAR_ACCESS === (self::OPTIMIZE_VAR_ACCESS & $this->optimizers) && !$env->isStrictVariables() && !$env->hasExtension('sandbox')) {
            if ($node instanceof Twix_Node_Body) {
                $this->inABody = false;
            } elseif ($this->inABody) {
                if (!$expression && get_class($node) !== 'Twix_Node' && $prependedNodes = array_shift($this->prependedNodes)) {
                    $nodes = array();
                    foreach (array_unique($prependedNodes) as $name) {
                        $nodes[] = new Twix_Node_SetTemp($name, $node->getLine());
                    }
                    $nodes[] = $node;
                    $node = new Twix_Node($nodes);
                }
            }
        }
        return $node;
    }
    protected function optimizeVariables(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if ('Twix_Node_Expression_Name' === get_class($node) && $node->isSimple()) {
            $this->prependedNodes[0][] = $node->getAttribute('name');
            return new Twix_Node_Expression_TempName($node->getAttribute('name'), $node->getLine());
        }
        return $node;
    }
    
    protected function optimizePrintNode(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if (!$node instanceof Twix_Node_Print) {
            return $node;
        }
        if (
            $node->getNode('expr') instanceof Twix_Node_Expression_BlockReference ||
            $node->getNode('expr') instanceof Twix_Node_Expression_Parent
        ) {
            $node->getNode('expr')->setAttribute('output', true);
            return $node->getNode('expr');
        }
        return $node;
    }
    
    protected function optimizeRawFilter(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_Expression_Filter && 'raw' == $node->getNode('filter')->getAttribute('value')) {
            return $node->getNode('node');
        }
        return $node;
    }
    
    protected function enterOptimizeFor(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_For) {
            // disable the loop variable by default
            $node->setAttribute('with_loop', false);
            array_unshift($this->loops, $node);
            array_unshift($this->loopsTargets, $node->getNode('value_target')->getAttribute('name'));
            array_unshift($this->loopsTargets, $node->getNode('key_target')->getAttribute('name'));
        } elseif (!$this->loops) {
            // we are outside a loop
            return;
        }
        // when do we need to add the loop variable back?
        // the loop variable is referenced for the current loop
        elseif ($node instanceof Twix_Node_Expression_Name && 'loop' === $node->getAttribute('name')) {
            $node->setAttribute('always_defined', true);
            $this->addLoopToCurrent();
        }
        // optimize access to loop targets
        elseif ($node instanceof Twix_Node_Expression_Name && in_array($node->getAttribute('name'), $this->loopsTargets)) {
            $node->setAttribute('always_defined', true);
        }
        // block reference
        elseif ($node instanceof Twix_Node_BlockReference || $node instanceof Twix_Node_Expression_BlockReference) {
            $this->addLoopToCurrent();
        }
        // include without the only attribute
        elseif ($node instanceof Twix_Node_Include && !$node->getAttribute('only')) {
            $this->addLoopToAll();
        }
        // include function without the with_context=false parameter
        elseif ($node instanceof Twix_Node_Expression_Function
            && 'include' === $node->getAttribute('name')
            && (!$node->getNode('arguments')->hasNode('with_context')
                 || false !== $node->getNode('arguments')->getNode('with_context')->getAttribute('value')
               )
        ) {
            $this->addLoopToAll();
        }
        // the loop variable is referenced via an attribute
        elseif ($node instanceof Twix_Node_Expression_GetAttr
            && (!$node->getNode('attribute') instanceof Twix_Node_Expression_Constant
                || 'parent' === $node->getNode('attribute')->getAttribute('value')
               )
            && (true === $this->loops[0]->getAttribute('with_loop')
                || ($node->getNode('node') instanceof Twix_Node_Expression_Name
                    && 'loop' === $node->getNode('node')->getAttribute('name')
                   )
               )
        ) {
            $this->addLoopToAll();
        }
    }
    
    protected function leaveOptimizeFor(Twix_NodeInterface $node, Twix_Environment $env)
    {
        if ($node instanceof Twix_Node_For) {
            array_shift($this->loops);
            array_shift($this->loopsTargets);
            array_shift($this->loopsTargets);
        }
    }
    protected function addLoopToCurrent()
    {
        $this->loops[0]->setAttribute('with_loop', true);
    }
    protected function addLoopToAll()
    {
        foreach ($this->loops as $loop) {
            $loop->setAttribute('with_loop', true);
        }
    }
    
    public function getPriority()
    {
        return 255;
    }
}

class Twix_Token
{
    protected $value;
    protected $type;
    protected $lineno;
    const EOF_TYPE = -1;
    const TEXT_TYPE = 0;
    const BLOCK_START_TYPE = 1;
    const VAR_START_TYPE = 2;
    const BLOCK_END_TYPE = 3;
    const VAR_END_TYPE = 4;
    const NAME_TYPE = 5;
    const NUMBER_TYPE = 6;
    const STRING_TYPE = 7;
    const OPERATOR_TYPE = 8;
    const PUNCTUATION_TYPE = 9;
    const INTERPOLATION_START_TYPE = 10;
    const INTERPOLATION_END_TYPE = 11;
    
    public function __construct($type, $value, $lineno)
    {
        $this->type = $type;
        $this->value = $value;
        $this->lineno = $lineno;
    }
    
    public function __toString()
    {
        return sprintf('%s(%s)', self::typeToString($this->type, true), $this->value);
    }
    
    public function test($type, $values = null)
    {
        if (null === $values && !is_int($type)) {
            $values = $type;
            $type = self::NAME_TYPE;
        }
        return ($this->type === $type) && (
            null === $values ||
            (is_array($values) && in_array($this->value, $values)) ||
            $this->value == $values
        );
    }
    
    public function getLine()
    {
        return $this->lineno;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public static function typeToString($type, $short = false)
    {
        switch ($type) {
            case self::EOF_TYPE:
                $name = 'EOF_TYPE';
                break;
            case self::TEXT_TYPE:
                $name = 'TEXT_TYPE';
                break;
            case self::BLOCK_START_TYPE:
                $name = 'BLOCK_START_TYPE';
                break;
            case self::VAR_START_TYPE:
                $name = 'VAR_START_TYPE';
                break;
            case self::BLOCK_END_TYPE:
                $name = 'BLOCK_END_TYPE';
                break;
            case self::VAR_END_TYPE:
                $name = 'VAR_END_TYPE';
                break;
            case self::NAME_TYPE:
                $name = 'NAME_TYPE';
                break;
            case self::NUMBER_TYPE:
                $name = 'NUMBER_TYPE';
                break;
            case self::STRING_TYPE:
                $name = 'STRING_TYPE';
                break;
            case self::OPERATOR_TYPE:
                $name = 'OPERATOR_TYPE';
                break;
            case self::PUNCTUATION_TYPE:
                $name = 'PUNCTUATION_TYPE';
                break;
            case self::INTERPOLATION_START_TYPE:
                $name = 'INTERPOLATION_START_TYPE';
                break;
            case self::INTERPOLATION_END_TYPE:
                $name = 'INTERPOLATION_END_TYPE';
                break;
            default:
                throw new LogicException(sprintf('Token of type "%s" does not exist.', $type));
        }
        return $short ? $name : 'Twix_Token::'.$name;
    }
    
    public static function typeToEnglish($type)
    {
        switch ($type) {
            case self::EOF_TYPE:
                return 'end of template';
            case self::TEXT_TYPE:
                return 'text';
            case self::BLOCK_START_TYPE:
                return 'begin of statement block';
            case self::VAR_START_TYPE:
                return 'begin of print statement';
            case self::BLOCK_END_TYPE:
                return 'end of statement block';
            case self::VAR_END_TYPE:
                return 'end of print statement';
            case self::NAME_TYPE:
                return 'name';
            case self::NUMBER_TYPE:
                return 'number';
            case self::STRING_TYPE:
                return 'string';
            case self::OPERATOR_TYPE:
                return 'operator';
            case self::PUNCTUATION_TYPE:
                return 'punctuation';
            case self::INTERPOLATION_START_TYPE:
                return 'begin of string interpolation';
            case self::INTERPOLATION_END_TYPE:
                return 'end of string interpolation';
            default:
                throw new LogicException(sprintf('Token of type "%s" does not exist.', $type));
        }
    }
}

class Twix_TokenStream
{
    protected $tokens;
    protected $current = 0;
    protected $filename;
    
    public function __construct(array $tokens, $filename = null)
    {
        $this->tokens = $tokens;
        $this->filename = $filename;
    }
    
    public function __toString()
    {
        return implode("\n", $this->tokens);
    }
    public function injectTokens(array $tokens)
    {
        $this->tokens = array_merge(array_slice($this->tokens, 0, $this->current), $tokens, array_slice($this->tokens, $this->current));
    }
    
    public function next()
    {
        if (!isset($this->tokens[++$this->current])) {
            throw new Twix_Error_Syntax('Unexpected end of template.', $this->tokens[$this->current - 1]->getLine(), $this->filename);
        }
        return $this->tokens[$this->current - 1];
    }
    
    public function nextIf($primary, $secondary = null)
    {
        if ($this->tokens[$this->current]->test($primary, $secondary)) {
            return $this->next();
        }
    }
    
    public function expect($type, $value = null, $message = null)
    {
        $token = $this->tokens[$this->current];
        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            throw new Twix_Error_Syntax(sprintf('%sUnexpected token "%s" of value "%s" ("%s" expected%s).',
                $message ? $message.'. ' : '',
                Twix_Token::typeToEnglish($token->getType()), $token->getValue(),
                Twix_Token::typeToEnglish($type), $value ? sprintf(' with value "%s"', $value) : ''),
                $line,
                $this->filename
            );
        }
        $this->next();
        return $token;
    }
    
    public function look($number = 1)
    {
        if (!isset($this->tokens[$this->current + $number])) {
            throw new Twix_Error_Syntax('Unexpected end of template.', $this->tokens[$this->current + $number - 1]->getLine(), $this->filename);
        }
        return $this->tokens[$this->current + $number];
    }
    
    public function test($primary, $secondary = null)
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }
    
    public function isEOF()
    {
        return $this->tokens[$this->current]->getType() === Twix_Token::EOF_TYPE;
    }
    
    public function getCurrent()
    {
        return $this->tokens[$this->current];
    }
    
    public function getFilename()
    {
        return $this->filename;
    }
}

interface Twix_ParserInterface
{
    
    public function parse(Twix_TokenStream $stream);
}

class Twix_Parser implements Twix_ParserInterface
{
    protected $stack = array();
    protected $stream;
    protected $parent;
    protected $handlers;
    protected $visitors;
    protected $expressionParser;
    protected $blocks;
    protected $blockStack;
    protected $macros;
    protected $env;
    protected $reservedMacroNames;
    protected $importedSymbols;
    protected $traits;
    protected $embeddedTemplates = array();
    
    public function __construct(Twix_Environment $env)
    {
        $this->env = $env;
    }
    public function getEnvironment()
    {
        return $this->env;
    }
    public function getVarName()
    {
        return sprintf('__internal_%s', hash('sha256', uniqid(mt_rand(), true), false));
    }
    public function getFilename()
    {
        return $this->stream->getFilename();
    }
    
    public function parse(Twix_TokenStream $stream, $test = null, $dropNeedle = false)
    {
        // push all variables into the stack to keep the current state of the parser
        // using get_object_vars() instead of foreach would lead to https://bugs.php.net/71336
        $vars = array();
        foreach ($this as $k => $v) {
            $vars[$k] = $v;
        }
        unset($vars['stack'], $vars['env'], $vars['handlers'], $vars['visitors'], $vars['expressionParser'], $vars['reservedMacroNames']);
        $this->stack[] = $vars;
        // tag handlers
        if (null === $this->handlers) {
            $this->handlers = $this->env->getTokenParsers();
            $this->handlers->setParser($this);
        }
        // node visitors
        if (null === $this->visitors) {
            $this->visitors = $this->env->getNodeVisitors();
        }
        if (null === $this->expressionParser) {
            $this->expressionParser = new Twix_ExpressionParser($this, $this->env->getUnaryOperators(), $this->env->getBinaryOperators());
        }
        $this->stream = $stream;
        $this->parent = null;
        $this->blocks = array();
        $this->macros = array();
        $this->traits = array();
        $this->blockStack = array();
        $this->importedSymbols = array(array());
        $this->embeddedTemplates = array();
        try {
            $body = $this->subparse($test, $dropNeedle);
            if (null !== $this->parent && null === $body = $this->filterBodyNodes($body)) {
                $body = new Twix_Node();
            }
        } catch (Twix_Error_Syntax $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($this->getFilename());
            }
            if (!$e->getTemplateLine()) {
                $e->setTemplateLine($this->stream->getCurrent()->getLine());
            }
            throw $e;
        }
        $node = new Twix_Node_Module(new Twix_Node_Body(array($body)), $this->parent, new Twix_Node($this->blocks), new Twix_Node($this->macros), new Twix_Node($this->traits), $this->embeddedTemplates, $this->getFilename());
        $traverser = new Twix_NodeTraverser($this->env, $this->visitors);
        $node = $traverser->traverse($node);
        // restore previous stack so previous parse() call can resume working
        foreach (array_pop($this->stack) as $key => $val) {
            $this->$key = $val;
        }
        return $node;
    }
    public function subparse($test, $dropNeedle = false)
    {
        $lineno = $this->getCurrentToken()->getLine();
        $rv = array();
        while (!$this->stream->isEOF()) {
            switch ($this->getCurrentToken()->getType()) {
                case Twix_Token::TEXT_TYPE:
                    $token = $this->stream->next();
                    $rv[] = new Twix_Node_Text($token->getValue(), $token->getLine());
                    break;
                case Twix_Token::VAR_START_TYPE:
                    $token = $this->stream->next();
                    $expr = $this->expressionParser->parseExpression();
                    $this->stream->expect(Twix_Token::VAR_END_TYPE);
                    $rv[] = new Twix_Node_Print($expr, $token->getLine());
                    break;
                case Twix_Token::BLOCK_START_TYPE:
                    $this->stream->next();
                    $token = $this->getCurrentToken();
                    if ($token->getType() !== Twix_Token::NAME_TYPE) {
                        throw new Twix_Error_Syntax('A block must start with a tag name.', $token->getLine(), $this->getFilename());
                    }
                    if (null !== $test && call_user_func($test, $token)) {
                        if ($dropNeedle) {
                            $this->stream->next();
                        }
                        if (1 === count($rv)) {
                            return $rv[0];
                        }
                        return new Twix_Node($rv, array(), $lineno);
                    }
                    $subparser = $this->handlers->getTokenParser($token->getValue());
                    if (null === $subparser) {
                        if (null !== $test) {
                            $e = new Twix_Error_Syntax(sprintf('Unexpected "%s" tag', $token->getValue()), $token->getLine(), $this->getFilename());
                            if (is_array($test) && isset($test[0]) && $test[0] instanceof Twix_TokenParserInterface) {
                                $e->appendMessage(sprintf(' (expecting closing tag for the "%s" tag defined near line %s).', $test[0]->getTag(), $lineno));
                            }
                        } else {
                            $e = new Twix_Error_Syntax(sprintf('Unknown "%s" tag.', $token->getValue()), $token->getLine(), $this->getFilename());
                            $e->addSuggestions($token->getValue(), array_keys($this->env->getTags()));
                        }
                        throw $e;
                    }
                    $this->stream->next();
                    $node = $subparser->parse($token);
                    if (null !== $node) {
                        $rv[] = $node;
                    }
                    break;
                default:
                    throw new Twix_Error_Syntax('Lexer or parser ended up in unsupported state.', 0, $this->getFilename());
            }
        }
        if (1 === count($rv)) {
            return $rv[0];
        }
        return new Twix_Node($rv, array(), $lineno);
    }
    public function addHandler($name, $class)
    {
        $this->handlers[$name] = $class;
    }
    public function addNodeVisitor(Twix_NodeVisitorInterface $visitor)
    {
        $this->visitors[] = $visitor;
    }
    public function getBlockStack()
    {
        return $this->blockStack;
    }
    public function peekBlockStack()
    {
        return $this->blockStack[count($this->blockStack) - 1];
    }
    public function popBlockStack()
    {
        array_pop($this->blockStack);
    }
    public function pushBlockStack($name)
    {
        $this->blockStack[] = $name;
    }
    public function hasBlock($name)
    {
        return isset($this->blocks[$name]);
    }
    public function getBlock($name)
    {
        return $this->blocks[$name];
    }
    public function setBlock($name, Twix_Node_Block $value)
    {
        $this->blocks[$name] = new Twix_Node_Body(array($value), array(), $value->getLine());
    }
    public function hasMacro($name)
    {
        return isset($this->macros[$name]);
    }
    public function setMacro($name, Twix_Node_Macro $node)
    {
        if ($this->isReservedMacroName($name)) {
            throw new Twix_Error_Syntax(sprintf('"%s" cannot be used as a macro name as it is a reserved keyword.', $name), $node->getLine(), $this->getFilename());
        }
        $this->macros[$name] = $node;
    }
    public function isReservedMacroName($name)
    {
        if (null === $this->reservedMacroNames) {
            $this->reservedMacroNames = array();
            $r = new ReflectionClass($this->env->getBaseTemplateClass());
            foreach ($r->getMethods() as $method) {
                $methodName = strtolower($method->getName());
                if ('get' === substr($methodName, 0, 3) && isset($methodName[3])) {
                    $this->reservedMacroNames[] = substr($methodName, 3);
                }
            }
        }
        return in_array(strtolower($name), $this->reservedMacroNames);
    }
    public function addTrait($trait)
    {
        $this->traits[] = $trait;
    }
    public function hasTraits()
    {
        return count($this->traits) > 0;
    }
    public function embedTemplate(Twix_Node_Module $template)
    {
        $template->setIndex(mt_rand());
        $this->embeddedTemplates[] = $template;
    }
    public function addImportedSymbol($type, $alias, $name = null, Twix_Node_Expression $node = null)
    {
        $this->importedSymbols[0][$type][$alias] = array('name' => $name, 'node' => $node);
    }
    public function getImportedSymbol($type, $alias)
    {
        foreach ($this->importedSymbols as $functions) {
            if (isset($functions[$type][$alias])) {
                return $functions[$type][$alias];
            }
        }
    }
    public function isMainScope()
    {
        return 1 === count($this->importedSymbols);
    }
    public function pushLocalScope()
    {
        array_unshift($this->importedSymbols, array());
    }
    public function popLocalScope()
    {
        array_shift($this->importedSymbols);
    }
    
    public function getExpressionParser()
    {
        return $this->expressionParser;
    }
    public function getParent()
    {
        return $this->parent;
    }
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    public function getStream()
    {
        return $this->stream;
    }
    
    public function getCurrentToken()
    {
        return $this->stream->getCurrent();
    }
    protected function filterBodyNodes(Twix_NodeInterface $node)
    {
        // check that the body does not contain non-empty output nodes
        if (
            ($node instanceof Twix_Node_Text && !ctype_space($node->getAttribute('data')))
            ||
            (!$node instanceof Twix_Node_Text && !$node instanceof Twix_Node_BlockReference && $node instanceof Twix_NodeOutputInterface)
        ) {
            if (false !== strpos((string) $node, chr(0xEF).chr(0xBB).chr(0xBF))) {
                throw new Twix_Error_Syntax('A template that extends another one cannot have a body but a byte order mark (BOM) has been detected; it must be removed.', $node->getLine(), $this->getFilename());
            }
            throw new Twix_Error_Syntax('A template that extends another one cannot have a body.', $node->getLine(), $this->getFilename());
        }
        // bypass "set" nodes as they "capture" the output
        if ($node instanceof Twix_Node_Set) {
            return $node;
        }
        if ($node instanceof Twix_NodeOutputInterface) {
            return;
        }
        foreach ($node as $k => $n) {
            if (null !== $n && null === $this->filterBodyNodes($n)) {
                $node->removeNode($k);
            }
        }
        return $node;
    }
}

interface Twix_NodeInterface extends Countable, IteratorAggregate
{
    
    public function compile(Twix_Compiler $compiler);
    public function getLine();
    public function getNodeTag();
}

class Twix_Node implements Twix_NodeInterface
{
    protected $nodes;
    protected $attributes;
    protected $lineno;
    protected $tag;
    
    public function __construct(array $nodes = array(), array $attributes = array(), $lineno = 0, $tag = null)
    {
        $this->nodes = $nodes;
        $this->attributes = $attributes;
        $this->lineno = $lineno;
        $this->tag = $tag;
    }
    public function __toString()
    {
        $attributes = array();
        foreach ($this->attributes as $name => $value) {
            $attributes[] = sprintf('%s: %s', $name, str_replace("\n", '', var_export($value, true)));
        }
        $repr = array(get_class($this).'('.implode(', ', $attributes));
        if (count($this->nodes)) {
            foreach ($this->nodes as $name => $node) {
                $len = strlen($name) + 4;
                $noderepr = array();
                foreach (explode("\n", (string) $node) as $line) {
                    $noderepr[] = str_repeat(' ', $len).$line;
                }
                $repr[] = sprintf('  %s: %s', $name, ltrim(implode("\n", $noderepr)));
            }
            $repr[] = ')';
        } else {
            $repr[0] .= ')';
        }
        return implode("\n", $repr);
    }
    
    public function toXml($asDom = false)
    {
        @trigger_error(sprintf('%s is deprecated since version 1.16.1 and will be removed in 2.0.', __METHOD__), E_USER_DEPRECATED);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->appendChild($xml = $dom->createElement('twig'));
        $xml->appendChild($node = $dom->createElement('node'));
        $node->setAttribute('class', get_class($this));
        foreach ($this->attributes as $name => $value) {
            $node->appendChild($attribute = $dom->createElement('attribute'));
            $attribute->setAttribute('name', $name);
            $attribute->appendChild($dom->createTextNode($value));
        }
        foreach ($this->nodes as $name => $n) {
            if (null === $n) {
                continue;
            }
            $child = $n->toXml(true)->getElementsByTagName('node')->item(0);
            $child = $dom->importNode($child, true);
            $child->setAttribute('name', $name);
            $node->appendChild($child);
        }
        return $asDom ? $dom : $dom->saveXML();
    }
    public function compile(Twix_Compiler $compiler)
    {
        foreach ($this->nodes as $node) {
            $node->compile($compiler);
        }
    }
    public function getLine()
    {
        return $this->lineno;
    }
    public function getNodeTag()
    {
        return $this->tag;
    }
    
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }
    
    public function getAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new LogicException(sprintf('Attribute "%s" does not exist for Node "%s".', $name, get_class($this)));
        }
        return $this->attributes[$name];
    }
    
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    
    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
    }
    
    public function hasNode($name)
    {
        return array_key_exists($name, $this->nodes);
    }
    
    public function getNode($name)
    {
        if (!array_key_exists($name, $this->nodes)) {
            throw new LogicException(sprintf('Node "%s" does not exist for Node "%s".', $name, get_class($this)));
        }
        return $this->nodes[$name];
    }
    
    public function setNode($name, $node = null)
    {
        $this->nodes[$name] = $node;
    }
    
    public function removeNode($name)
    {
        unset($this->nodes[$name]);
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->nodes);
    }
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }
}

interface Twix_NodeOutputInterface
{
}

class Twix_Node_Text extends Twix_Node implements Twix_NodeOutputInterface
{
    public function __construct($data, $lineno)
    {
        parent::__construct(array(), array('data' => $data), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $text = $this->getAttribute('data');
        if (strpos($text, '<?') !== false)
        $compiler
            ->write('echo ')
            ->string($this->getAttribute('data'))
            ->raw(";\n");
        else $compiler
            ->raw('?>'.$this->getAttribute('data')."<?php\n");
    }
}

abstract class Twix_Node_Expression extends Twix_Node
{
}

class Twix_Node_Expression_Name extends Twix_Node_Expression
{
    protected $specialVars = array(
        '_self' => '$this',
        '_context' => '@$context',
        '_charset' => '$this->env->getCharset()',
    );
    public function __construct($name, $lineno)
    {
        parent::__construct(array(), array('name' => $name, 'is_defined_test' => false, 'ignore_strict_check' => false, 'always_defined' => false), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        if ($this->getAttribute('is_defined_test')) {
            if ($this->isSpecial()) {
                $compiler->repr(true);
            } else {
                $compiler->raw('isset($'.$name.')');
            }
        } elseif ($this->isSpecial()) {
            $compiler->raw($this->specialVars[$name]);
        } elseif ($this->getAttribute('always_defined')) { // MX
            $compiler
                //->raw('$context[')
                //->string($name)
                //->raw(']')
                ->raw('@$'.$name); //MaxD
            ;
        } else {
            // remove the non-PHP 5.4 version when PHP 5.3 support is dropped
            // as the non-optimized version is just a workaround for slow ternary operator
            // when the context has a lot of variables
            if (PHP_VERSION_ID >= 50400) {
                // PHP 5.4 ternary operator performance was optimized
                $compiler->raw('@$'.$name); //MaxD
                /*$compiler
                    ->raw('(isset($context[')
                    ->string($name)
                    ->raw(']) ? $context[')
                    ->string($name)
                    ->raw('] : ')
                ;
                if ($this->getAttribute('ignore_strict_check') || !$compiler->getEnvironment()->isStrictVariables()) {
                    $compiler->raw('null)');
                } else {
                    $compiler->raw('$this->getContext($context, ')->string($name)->raw('))');
                }
                */
            } else {
                $compiler
                    ->raw('$this->getContext($context, ')
                    ->string($name)
                ;
                if ($this->getAttribute('ignore_strict_check')) {
                    $compiler->raw(', true');
                }
                $compiler
                    ->raw(')')
                ;
            }
        }
    }
    public function isSpecial()
    {
        return isset($this->specialVars[$this->getAttribute('name')]);
    }
    public function isSimple()
    {
        return !$this->isSpecial() && !$this->getAttribute('is_defined_test');
    }
}

class Twix_Node_Print extends Twix_Node implements Twix_NodeOutputInterface
{
    public function __construct(Twix_Node_Expression $expr, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('echo ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
    }
}

class Twix_Node_Expression_AssignName extends Twix_Node_Expression_Name
{
    public function compile(Twix_Compiler $compiler)
    {
        /*$compiler
            ->raw('$context[')
            ->string($this->getAttribute('name'))
            ->raw(']')
        ;*/
        $compiler
            ->raw('$'.$this->getAttribute('name')); //MaxD
    }
}

class Twix_Node_Expression_Array extends Twix_Node_Expression
{
    protected $index;
    public function __construct(array $elements, $lineno)
    {
        parent::__construct($elements, array(), $lineno);
        $this->index = -1;
        foreach ($this->getKeyValuePairs() as $pair) {
            if ($pair['key'] instanceof Twix_Node_Expression_Constant && ctype_digit((string) $pair['key']->getAttribute('value')) && $pair['key']->getAttribute('value') > $this->index) {
                $this->index = $pair['key']->getAttribute('value');
            }
        }
    }
    public function getKeyValuePairs()
    {
        $pairs = array();
        foreach (array_chunk($this->nodes, 2) as $pair) {
            $pairs[] = array(
                'key' => $pair[0],
                'value' => $pair[1],
            );
        }
        return $pairs;
    }
    public function hasElement(Twix_Node_Expression $key)
    {
        foreach ($this->getKeyValuePairs() as $pair) {
            // we compare the string representation of the keys
            // to avoid comparing the line numbers which are not relevant here.
            if ((string) $key == (string) $pair['key']) {
                return true;
            }
        }
        return false;
    }
    public function addElement(Twix_Node_Expression $value, Twix_Node_Expression $key = null)
    {
        if (null === $key) {
            $key = new Twix_Node_Expression_Constant(++$this->index, $value->getLine());
        }
        array_push($this->nodes, $key, $value);
    }
    public function compile(Twix_Compiler $compiler)
    {
        if (end($compiler->mxraw) ===2) { //Mx
            $first = true;
            foreach ($this->getKeyValuePairs() as $pair) {
                if (!$pair['value']) continue;
                if (!$first) {
                    $compiler->raw(', ');
                }
                $first = false;
                $compiler
                    ->subcompile($pair['value'])
                ;
            }
            return;
        }
        $compiler->raw('array(');
        $first = true;
        foreach ($this->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;
            $compiler
                ->subcompile($pair['key'])
                ->raw(' => ')
                ->subcompile($pair['value'])
            ;
        }
        $compiler->raw(')');
    }
}

class Twix_Node_Expression_Constant extends Twix_Node_Expression
{
    public function __construct($value, $lineno)
    {
        parent::__construct(array(), array('value' => $value), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->repr($this->getAttribute('value'));
    }
}

class Twix_Node_Expression_GetAttr extends Twix_Node_Expression
{
    public function __construct(Twix_Node_Expression $node, Twix_Node_Expression $attribute, Twix_Node_Expression $arguments = null, $type, $lineno)
    {
        parent::__construct(array('node' => $node, 'attribute' => $attribute, 'arguments' => $arguments), array('type' => $type, 'is_defined_test' => false, 'ignore_strict_check' => false, 'disable_c_ext' => false), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        //if (function_exists('twix_template_get_attributes') && !$this->getAttribute('disable_c_ext')) {
        //    $compiler->raw('twix_template_get_attributes($this, ');
        //} else {
            //$compiler->raw('$this->getAttribute(');
            //MaxD
        //}

        $a = $this->getNode('arguments');
        $is_object = (($a && $a->nodes) || (!empty($this->attributes["type"]) && $this->attributes["type"]=="method"));

        if ($this->getAttribute('is_defined_test') && !$is_object) $compiler->raw('isset(');

        if ($this->getAttribute('ignore_strict_check')) {
            $this->getNode('node')->setAttribute('ignore_strict_check', true);
        }
        $compiler->subcompile($this->getNode('node'));
        //$compiler->raw(', ')->subcompile($this->getNode('attribute'));

        if ($is_object) {
            $compiler->CheckObjects();
            $compiler->raw('->')->subcompile($this->getNode('attribute'), 2)->raw('(');
            $compiler->subcompile($this->getNode('arguments'), 2);
            $compiler->raw(')'); //MaxD4
        }
        else {
            $node_name = $this->getNode('node');
            if ($node_name->hasAttribute('name')) {
                $node_name  = $node_name->getAttribute('name');
                if ($node_name == 'journal3')
                    $is_object = true;
            }

            if ($is_object) $compiler->raw('->')->subcompile($this->getNode('attribute'), 2);
            else $compiler->raw('[')->subcompile($this->getNode('attribute'))->raw(']');
        } //MaxD

        if ($this->getAttribute('is_defined_test') && !$is_object) $compiler->raw(')');
        
        //$compiler->raw(')');
    }
}

class Twix_Node_For extends Twix_Node
{
    protected $loop;
    public function __construct(Twix_Node_Expression_AssignName $keyTarget, Twix_Node_Expression_AssignName $valueTarget, Twix_Node_Expression $seq, Twix_Node_Expression $ifexpr = null, Twix_NodeInterface $body, Twix_NodeInterface $else = null, $lineno, $tag = null)
    {
        $body = new Twix_Node(array($body, $this->loop = new Twix_Node_ForLoop($lineno, $tag)));
        if (null !== $ifexpr) {
            $body = new Twix_Node_If(new Twix_Node(array($ifexpr, $body)), null, $lineno, $tag);
        }
        parent::__construct(array('key_target' => $keyTarget, 'value_target' => $valueTarget, 'seq' => $seq, 'body' => $body, 'else' => $else), array('with_loop' => true, 'ifexpr' => null !== $ifexpr), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $r = rand(10000, 99999);

        $compiler

            //MaxD
            ->write("\$save$r = @")
            ->subcompile($this->getNode('value_target'))
            ->write(";\n")

            ->write("\$context['_parent'] = \$context;\n")
            ->write("\$context['_seq'] = twix_ensure_traversable(")
            ->subcompile($this->getNode('seq'))
            ->raw(");\n")
        ;
        if (null !== $this->getNode('else')) {
            $compiler->write("\$context['_iterated'] = false;\n");
        }
        if ($this->getAttribute('with_loop')) {
            $compiler
                ->write("\$loop = array(\n")
                ->write("  'parent' => \$context['_parent'],\n")
                ->write("  'index0' => 0,\n")
                ->write("  'index'  => 1,\n")
                ->write("  'first'  => true,\n")
                ->write(");\n")
            ;
            if (!$this->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (is_array(\$context['_seq']) || (is_object(\$context['_seq']) && \$context['_seq'] instanceof Countable)) {\n")
                    ->indent()
                    ->write("\$length = count(\$context['_seq']);\n")
                    ->write("\$loop['revindex0'] = \$length - 1;\n")
                    ->write("\$loop['revindex'] = \$length;\n")
                    ->write("\$loop['length'] = \$length;\n")
                    ->write("\$loop['last'] = 1 === \$length;\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }
        $this->loop->setAttribute('else', null !== $this->getNode('else'));
        $this->loop->setAttribute('with_loop', $this->getAttribute('with_loop'));
        $this->loop->setAttribute('ifexpr', $this->getAttribute('ifexpr'));
        $compiler
            ->write("foreach (\$context['_seq'] as ")
            ->subcompile($this->getNode('key_target'))
            ->raw(' => ')
            ->subcompile($this->getNode('value_target'))
            ->raw(") {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("}\n")
        ;
        if (null !== $this->getNode('else')) {
            $compiler
                ->write("if (!\$context['_iterated']) {\n")
                ->indent()
                ->subcompile($this->getNode('else'))
                ->outdent()
                ->write("}\n")
            ;
        }
        $compiler->write("\$_parent = \$context['_parent'];\n");
        // remove some "private" loop variables (needed for nested loops)
        $compiler->write('unset($context[\'_seq\'], $context[\'_iterated\'], $context[\''.$this->getNode('key_target')->getAttribute('name').'\'], $context[\''.$this->getNode('value_target')->getAttribute('name').'\'], $context[\'_parent\'], $context[\'loop\']);'."\n");
        // keep the values set in the inner context for variables defined in the outer context
        $compiler->write("\$context = array_intersect_key(\$context, \$_parent) + \$_parent;\n");

        //MaxD
        $compiler
            ->subcompile($this->getNode('value_target'))
            ->write(" = \$save$r;\n");
    }
}

class Twix_Node_ForLoop extends Twix_Node
{
    public function __construct($lineno, $tag = null)
    {
        parent::__construct(array(), array('with_loop' => false, 'ifexpr' => false, 'else' => false), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        if ($this->getAttribute('else')) {
            $compiler->write("\$context['_iterated'] = true;\n");
        }
        if ($this->getAttribute('with_loop')) {
            $compiler
                ->write("++\$loop['index0'];\n")
                ->write("++\$loop['index'];\n")
                ->write("\$loop['first'] = false;\n")
            ;
            if (!$this->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (isset(\$loop['length'])) {\n")
                    ->indent()
                    ->write("--\$loop['revindex0'];\n")
                    ->write("--\$loop['revindex'];\n")
                    ->write("\$loop['last'] = 0 === \$loop['revindex0'];\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }
    }
}

class Twix_Node_If extends Twix_Node
{
    public function __construct(Twix_NodeInterface $tests, Twix_NodeInterface $else = null, $lineno, $tag = null)
    {
        parent::__construct(array('tests' => $tests, 'else' => $else), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        for ($i = 0, $count = count($this->getNode('tests')); $i < $count; $i += 2) {
            if ($i > 0) {
                $compiler
                    ->outdent()
                    ->write('} elseif (')
                ;
            } else {
                $compiler
                    ->write('if (')
                ;
            }
            $compiler
                ->subcompile($this->getNode('tests')->getNode($i))
                ->raw(") {\n")
                ->indent()
                ->subcompile($this->getNode('tests')->getNode($i + 1))
            ;
        }
        if ($this->hasNode('else') && null !== $this->getNode('else')) {
            $compiler
                ->outdent()
                ->write("} else {\n")
                ->indent()
                ->subcompile($this->getNode('else'))
            ;
        }
        $compiler
            ->outdent()
            ->write("}\n");
    }
}

class Twix_Node_Module extends Twix_Node
{
    public function __construct(Twix_NodeInterface $body, Twix_Node_Expression $parent = null, Twix_NodeInterface $blocks, Twix_NodeInterface $macros, Twix_NodeInterface $traits, $embeddedTemplates, $filename)
    {
        // embedded templates are set as attributes so that they are only visited once by the visitors
        parent::__construct(array(
            'parent' => $parent,
            'body' => $body,
            'blocks' => $blocks,
            'macros' => $macros,
            'traits' => $traits,
            'display_start' => new Twix_Node(),
            'display_end' => new Twix_Node(),
            'constructor_start' => new Twix_Node(),
            'constructor_end' => new Twix_Node(),
            'class_end' => new Twix_Node(),
        ), array(
            'filename' => $filename,
            'index' => null,
            'embedded_templates' => $embeddedTemplates,
        ), 1);
    }
    public function setIndex($index)
    {
        $this->setAttribute('index', $index);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $this->compileTemplate($compiler);
        foreach ($this->getAttribute('embedded_templates') as $template) {
            $compiler->subcompile($template);
        }
    }
    protected function compileTemplate(Twix_Compiler $compiler)
    {
        if (!$this->getAttribute('index')) {
            $compiler->write('<?php');
        }
        $this->compileClassHeader($compiler);
        if (
            count($this->getNode('blocks'))
            || count($this->getNode('traits'))
            || null === $this->getNode('parent')
            || $this->getNode('parent') instanceof Twix_Node_Expression_Constant
            || count($this->getNode('constructor_start'))
            || count($this->getNode('constructor_end'))
        ) {
            $this->compileConstructor($compiler);
        }
        $this->compileGetParent($compiler);
        $this->compileDisplay($compiler);
        $compiler->subcompile($this->getNode('blocks'));
        $this->compileMacros($compiler);
        $this->compileGetTemplateName($compiler);
        $this->compileIsTraitable($compiler);
        $this->compileDebugInfo($compiler);
        $this->compileClassFooter($compiler);
    }
    protected function compileGetParent(Twix_Compiler $compiler)
    {
        if (null === $parent = $this->getNode('parent')) {
            return;
        }
        $compiler
            ->write("protected function doGetParent(array \$context)\n", "{\n")
            ->indent()
            ->addDebugInfo($parent)
            ->write('return ')
        ;
        if ($parent instanceof Twix_Node_Expression_Constant) {
            $compiler->subcompile($parent);
        } else {
            $compiler
                ->raw('$this->loadTemplate(')
                ->subcompile($parent)
                ->raw(')') //MaxD
            ;
        }
        $compiler
            ->raw(";\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
    protected function compileClassHeader(Twix_Compiler $compiler)
    {
        $compiler
            ->write("\n\n")
            // if the filename contains */, add a blank to avoid a PHP parse error
            ->write("/* _source_ */\n")
            ->write('class '.$compiler->getEnvironment()->getTemplateClass($this->getAttribute('filename'), $this->getAttribute('index')))
            ->raw(sprintf(" extends %s\n", $compiler->getEnvironment()->getBaseTemplateClass()))
            ->write("{\n")
            ->indent()
        ;
    }
    protected function compileConstructor(Twix_Compiler $compiler)
    {
        $compiler
            ->write("public function __construct(Twix_Environment \$env)\n", "{\n")
            ->indent()
            ->subcompile($this->getNode('constructor_start'))
            ->write("parent::__construct(\$env);\n\n")
        ;
        // parent
        if (null === $parent = $this->getNode('parent')) {
            $compiler->write("\$this->parent = false;\n\n");
        } elseif ($parent instanceof Twix_Node_Expression_Constant) {
            $compiler
                ->addDebugInfo($parent)
                ->write('$this->parent = $this->loadTemplate(')
                ->subcompile($parent)
                ->raw(', ')
                ->repr($compiler->getFilename())
                ->raw(', ')
                ->repr($this->getNode('parent')->getLine())
                ->raw(");\n")
            ;
        }
        $countTraits = count($this->getNode('traits'));
        if ($countTraits) {
            // traits
            foreach ($this->getNode('traits') as $i => $trait) {
                $this->compileLoadTemplate($compiler, $trait->getNode('template'), sprintf('$_trait_%s', $i));
                $compiler
                    ->addDebugInfo($trait->getNode('template'))
                    ->write(sprintf("if (!\$_trait_%s->isTraitable()) {\n", $i))
                    ->indent()
                    ->write("throw new Twix_Error_Runtime('Template \"'.")
                    ->subcompile($trait->getNode('template'))
                    ->raw(".'\" cannot be used as a trait.');\n")
                    ->outdent()
                    ->write("}\n")
                    ->write(sprintf("\$_trait_%s_blocks = \$_trait_%s->getBlocks();\n\n", $i, $i))
                ;
                foreach ($trait->getNode('targets') as $key => $value) {
                    $compiler
                        ->write(sprintf('if (!isset($_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw("])) {\n")
                        ->indent()
                        ->write("throw new Twix_Error_Runtime(sprintf('Block ")
                        ->string($key)
                        ->raw(' is not defined in trait ')
                        ->subcompile($trait->getNode('template'))
                        ->raw(".'));\n")
                        ->outdent()
                        ->write("}\n\n")
                        ->write(sprintf('$_trait_%s_blocks[', $i))
                        ->subcompile($value)
                        ->raw(sprintf('] = $_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw(sprintf(']; unset($_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw("]);\n\n")
                    ;
                }
            }
            if ($countTraits > 1) {
                $compiler
                    ->write("\$this->traits = array_merge(\n")
                    ->indent()
                ;
                for ($i = 0; $i < $countTraits; ++$i) {
                    $compiler
                        ->write(sprintf('$_trait_%s_blocks'.($i == $countTraits - 1 ? '' : ',')."\n", $i))
                    ;
                }
                $compiler
                    ->outdent()
                    ->write(");\n\n")
                ;
            } else {
                $compiler
                    ->write("\$this->traits = \$_trait_0_blocks;\n\n")
                ;
            }
            $compiler
                ->write("\$this->blocks = array_merge(\n")
                ->indent()
                ->write("\$this->traits,\n")
                ->write("array(\n")
            ;
        } else {
            $compiler
                ->write("\$this->blocks = array(\n")
            ;
        }
        // blocks
        $compiler
            ->indent()
        ;
        foreach ($this->getNode('blocks') as $name => $node) {
            $compiler
                ->write(sprintf("'%s' => array(\$this, 'block_%s'),\n", $name, $name))
            ;
        }
        if ($countTraits) {
            $compiler
                ->outdent()
                ->write(")\n")
            ;
        }
        $compiler
            ->outdent()
            ->write(");\n")
            ->outdent()
            ->subcompile($this->getNode('constructor_end'))
            ->write("}\n\n")
        ;
    }
    protected function compileDisplay(Twix_Compiler $compiler)
    {
        $compiler
            ->write("protected function doDisplay(array \$context, array \$blocks = array())\n", "{\n")
            ->write("extract(\$context); ") //MaxD
            ->indent()
            ->subcompile($this->getNode('display_start'))
            ->subcompile($this->getNode('body'))
        ;
        if (null !== $parent = $this->getNode('parent')) {
            $compiler->addDebugInfo($parent);
            if ($parent instanceof Twix_Node_Expression_Constant) {
                $compiler->write('$this->parent');
            } else {
                $compiler->write('$this->getParent($context)');
            }
            $compiler->raw("->display(\$context, array_merge(\$this->blocks, \$blocks));\n");
        }
        $compiler
            ->subcompile($this->getNode('display_end'))
            ->outdent()
            ->write("}\n\n")
        ;
    }
    protected function compileClassFooter(Twix_Compiler $compiler)
    {
        $compiler
            ->subcompile($this->getNode('class_end'))
            ->outdent()
            ->write("}\n")
        ;
    }
    protected function compileMacros(Twix_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('macros'));
    }
    protected function compileGetTemplateName(Twix_Compiler $compiler)
    {
        $compiler
            ->write("public function getTemplateName()\n", "{\n")
            ->indent()
            ->write('return ')
            ->repr($this->getAttribute('filename'))
            ->raw(";\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
    protected function compileIsTraitable(Twix_Compiler $compiler)
    {
        // A template can be used as a trait if:
        //   * it has no parent
        //   * it has no macros
        //   * it has no body
        //
        // Put another way, a template can be used as a trait if it
        // only contains blocks and use statements.
        $traitable = null === $this->getNode('parent') && 0 === count($this->getNode('macros'));
        if ($traitable) {
            if ($this->getNode('body') instanceof Twix_Node_Body) {
                $nodes = $this->getNode('body')->getNode(0);
            } else {
                $nodes = $this->getNode('body');
            }
            if (!count($nodes)) {
                $nodes = new Twix_Node(array($nodes));
            }
            foreach ($nodes as $node) {
                if (!$node || @!count($node)) {
                    continue;
                }
                if ($node instanceof Twix_Node_Text && ctype_space($node->getAttribute('data'))) {
                    continue;
                }
                if ($node instanceof Twix_Node_BlockReference) {
                    continue;
                }
                $traitable = false;
                break;
            }
        }
        if ($traitable) {
            return;
        }
        $compiler
            ->write("public function isTraitable()\n", "{\n")
            ->indent()
            ->write(sprintf("return %s;\n", $traitable ? 'true' : 'false'))
            ->outdent()
            ->write("}\n\n")
        ;
    }
    protected function compileDebugInfo(Twix_Compiler $compiler)
    {
        $compiler
            ->write("public function getDebugInfo()\n", "{\n")
            ->indent()
            ->write(sprintf("return %s;\n", str_replace("\n", '', var_export(array_reverse($compiler->getDebugInfo(), true), true))))
            ->outdent()
            ->write("}\n")
        ;
    }
    protected function compileLoadTemplate(Twix_Compiler $compiler, $node, $var)
    {
        if ($node instanceof Twix_Node_Expression_Constant) {
            $compiler
                ->write(sprintf('%s = $this->loadTemplate(', $var))
                ->subcompile($node)
                ->raw(', ')
                ->repr($compiler->getFilename())
                ->raw(', ')
                ->repr($node->getLine())
                ->raw(");\n")
            ;
        } else {
            throw new LogicException('Trait templates can only be constant nodes');
        }
    }
}

class Twix_Node_Body extends Twix_Node
{
}

class Twix_NodeTraverser
{
    protected $env;
    protected $visitors = array();
    
    public function __construct(Twix_Environment $env, array $visitors = array())
    {
        $this->env = $env;
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }
    
    public function addVisitor(Twix_NodeVisitorInterface $visitor)
    {
        if (!isset($this->visitors[$visitor->getPriority()])) {
            $this->visitors[$visitor->getPriority()] = array();
        }
        $this->visitors[$visitor->getPriority()][] = $visitor;
    }
    
    public function traverse(Twix_NodeInterface $node)
    {
        ksort($this->visitors);
        foreach ($this->visitors as $visitors) {
            foreach ($visitors as $visitor) {
                $node = $this->traverseForVisitor($visitor, $node);
            }
        }
        return $node;
    }
    protected function traverseForVisitor(Twix_NodeVisitorInterface $visitor, Twix_NodeInterface $node = null)
    {
        if (null === $node) {
            return;
        }
        $node = $visitor->enterNode($node, $this->env);
        foreach ($node as $k => $n) {
            if (false !== $n = $this->traverseForVisitor($visitor, $n)) {
                $node->setNode($k, $n);
            } else {
                $node->removeNode($k);
            }
        }
        return $visitor->leaveNode($node, $this->env);
    }
}

interface Twix_CompilerInterface
{
    
    public function compile(Twix_NodeInterface $node);
    
    public function getSource();
}

#[\AllowDynamicProperties]
class Twix_Compiler implements Twix_CompilerInterface
{
    protected $lastLine;
    protected $source;
    protected $indentation;
    protected $env;
    protected $debugInfo = array();
    protected $sourceOffset;
    protected $sourceLine;
    protected $filename;
    
    public function __construct(Twix_Environment $env)
    {
        $this->env = $env;
    }
    public function getFilename()
    {
        return $this->filename;
    }
    
    public function getEnvironment()
    {
        return $this->env;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function compile(Twix_NodeInterface $node, $indentation = 0)
    {
        $this->lastLine = null;
        $this->source = '';
        $this->debugInfo = array();
        $this->sourceOffset = 0;
        // source code starts at 1 (as we then increment it when we encounter new lines)
        $this->sourceLine = 1;
        $this->indentation = $indentation;
        if ($node instanceof Twix_Node_Module) {
            $this->filename = $node->getAttribute('filename');
        }
        $node->compile($this);
        return $this;
    }
    public function subcompile(Twix_NodeInterface $node, $raw = true)
    {
        if (false === $raw) {
            $this->addIndentation();
        }
        $this->mxraw[] = $raw;
        $node->compile($this);
        array_pop($this->mxraw);
        return $this;
    }
    
    public function raw($string)
    {
        $this->source .= $string;
        return $this;
    }
    
    public function write()
    {
        $strings = func_get_args();
        foreach ($strings as $string) {
            $this->addIndentation();
            $this->source .= $string;
        }
        return $this;
    }
    
    public function addIndentation()
    {
        $this->source .= str_repeat(' ', $this->indentation * 4);
        return $this;
    }
    public function CheckObjects() { //MX
        $p = strlen($this->source)-2;
        while (substr($this->source, $p, 2) == '"]') {
            $l = strrpos($this->source, '["');
            $this->source = substr($this->source, 0, $l).'->'.substr($this->source, $l+2, $p-$l-2);
            $p = $l-2;
        }
    }
    
    public function string($value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));
        return $this;
    }
    
    public function repr($value)
    {
        if ($value=='isPopup')
            $x=1;
        if (is_int($value) || is_float($value)) {
            if (false !== $locale = setlocale(LC_NUMERIC, 0)) {
                setlocale(LC_NUMERIC, 'C');
            }
            $this->raw($value);
            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            if (end($this->mxraw) === 2) { //Mx
                $first = true;
                foreach ($value as $key => $v) {
                    if (!$v) continue;
                    if (!$first) {
                        $this->raw(', ');
                    }
                    $first = false;
                    $this->repr($v);
                }
            } else {
                $this->raw('array(');
                $first = true;
                foreach ($value as $key => $v) {
                    if (!$first) {
                        $this->raw(', ');
                    }
                    $first = false;
                    $this->repr($key);
                    $this->raw(' => ');
                    $this->repr($v);
                }
                $this->raw(')');
            }
        } else {
            if (end($this->mxraw) === 2) $this->raw($value); //Mx
            else
                $this->string($value);
        }
        return $this;
    }
    
    public function addDebugInfo(Twix_NodeInterface $node)
    {
        if ($node->getLine() != $this->lastLine) {
            $this->write(sprintf("// line %d\n", $node->getLine()));
            // when mbstring.func_overload is set to 2
            // mb_substr_count() replaces substr_count()
            // but they have different signatures!
            if (((int) ini_get('mbstring.func_overload')) & 2) {
                // this is much slower than the "right" version
                $this->sourceLine += mb_substr_count(mb_substr($this->source, $this->sourceOffset), "\n");
            } else {
                $this->sourceLine += substr_count($this->source, "\n", $this->sourceOffset);
            }
            $this->sourceOffset = strlen($this->source);
            $this->debugInfo[$this->sourceLine] = $node->getLine();
            $this->lastLine = $node->getLine();
        }
        return $this;
    }
    public function getDebugInfo()
    {
        ksort($this->debugInfo);
        return $this->debugInfo;
    }
    
    public function indent($step = 1)
    {
        $this->indentation += $step;
        return $this;
    }
    
    public function outdent($step = 1)
    {
        // can't outdent by more steps than the current indentation level
        if ($this->indentation < $step) {
            throw new LogicException('Unable to call outdent() as the indentation would become negative');
        }
        $this->indentation -= $step;
        return $this;
    }
    public function getVarName()
    {
        return sprintf('__internal_%s', hash('sha256', uniqid(mt_rand(), true), false));
    }
}

abstract class Twix_Node_Expression_Binary extends Twix_Node_Expression
{
    public function __construct(Twix_NodeInterface $left, Twix_NodeInterface $right, $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ')
        ;
        $this->operator($compiler);
        $compiler
            ->raw(' ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }
    abstract public function operator(Twix_Compiler $compiler);
}

class Twix_Node_Expression_Binary_Greater extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('>');
    }
}

class Twix_Node_Expression_Binary_Less extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('<');
    }
}

class Twix_Node_Set extends Twix_Node
{
    public function __construct($capture, Twix_NodeInterface $names, Twix_NodeInterface $values, $lineno, $tag = null)
    {
        parent::__construct(array('names' => $names, 'values' => $values), array('capture' => $capture, 'safe' => false), $lineno, $tag);
        
        if ($this->getAttribute('capture')) {
            $this->setAttribute('safe', true);
            $values = $this->getNode('values');
            if ($values instanceof Twix_Node_Text) {
                $this->setNode('values', new Twix_Node_Expression_Constant($values->getAttribute('data'), $values->getLine()));
                $this->setAttribute('capture', false);
            }
        }
    }
    public function compile(Twix_Compiler $compiler)
    {
        if (count($this->getNode('names')) > 1) {
            $compiler->write('list(');
            foreach ($this->getNode('names') as $idx => $node) {
                if ($idx) {
                    $compiler->raw(', ');
                }
                $compiler->subcompile($node);
            }
            $compiler->raw(')');
        } else {
            if ($this->getAttribute('capture')) {
                $compiler
                    ->write("ob_start();\n")
                    ->subcompile($this->getNode('values'))
                ;
            }
            $compiler->subcompile($this->getNode('names'), false);
            if ($this->getAttribute('capture')) {
                $compiler->raw(" = ('' === \$tmp = ob_get_clean()) ? '' : new Twix_Markup(\$tmp, \$this->env->getCharset())");
            }
        }
        if (!$this->getAttribute('capture')) {
            $compiler->raw(' = ');
            if (count($this->getNode('names')) > 1) {
                $compiler->write('array(');
                foreach ($this->getNode('values') as $idx => $value) {
                    if ($idx) {
                        $compiler->raw(', ');
                    }
                    $compiler->subcompile($value);
                }
                $compiler->raw(')');
            } else {
                if ($this->getAttribute('safe')) {
                    $compiler
                        ->raw("('' === \$tmp = ")
                        ->subcompile($this->getNode('values'))
                        ->raw(") ? '' : new Twix_Markup(\$tmp, \$this->env->getCharset())")
                    ;
                } else {
                    $compiler->subcompile($this->getNode('values'));
                }
            }
        }
        $compiler->raw(";\n");
    }
}

class Twix_Node_Expression_Binary_Add extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('+');
    }
}

abstract class Twix_Node_Expression_Call extends Twix_Node_Expression
{
    protected function compileCallable(Twix_Compiler $compiler)
    {
        $closingParenthesis = false;
        if ($this->hasAttribute('callable') && $callable = $this->getAttribute('callable')) {
            if (is_string($callable)) {
                $compiler->raw($callable);
            } elseif (is_array($callable) && $callable[0] instanceof Twix_ExtensionInterface) {
                $compiler->raw(sprintf('$this->env->getExtension(\'%s\')->%s', $callable[0]->getName(), $callable[1]));
            } else {
                $type = ucfirst($this->getAttribute('type'));
                $compiler->raw(sprintf('call_user_func_array($this->env->get%s(\'%s\')->getCallable(), array', $type, $this->getAttribute('name')));
                $closingParenthesis = true;
            }
        } else {
            $compiler->raw($this->getAttribute('thing')->compile());
        }
        $this->compileArguments($compiler);
        if ($closingParenthesis) {
            $compiler->raw(')');
        }
    }
    protected function compileArguments(Twix_Compiler $compiler)
    {
        $compiler->raw('(');
        $first = true;
        if ($this->hasAttribute('needs_environment') && $this->getAttribute('needs_environment')) {
            $compiler->raw('$this->env');
            $first = false;
        }
        if ($this->hasAttribute('needs_context') && $this->getAttribute('needs_context')) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $compiler->raw('$context');
            $first = false;
        }
        if ($this->hasAttribute('arguments')) {
            foreach ($this->getAttribute('arguments') as $argument) {
                if (!$first) {
                    $compiler->raw(', ');
                }
                $compiler->string($argument);
                $first = false;
            }
        }
        if ($this->hasNode('node')) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $compiler->subcompile($this->getNode('node'));
            $first = false;
        }
        if ($this->hasNode('arguments') && null !== $this->getNode('arguments')) {
            $callable = $this->hasAttribute('callable') ? $this->getAttribute('callable') : null;
            $arguments = $this->getArguments($callable, $this->getNode('arguments'));
            foreach ($arguments as $node) {
                if (!$first) {
                    $compiler->raw(', ');
                }
                $compiler->subcompile($node);
                $first = false;
            }
        }
        $compiler->raw(')');
    }
    protected function getArguments($callable, $arguments)
    {
        $callType = $this->getAttribute('type');
        $callName = $this->getAttribute('name');
        $parameters = array();
        $named = false;
        foreach ($arguments as $name => $node) {
            if (!is_int($name)) {
                $named = true;
                $name = $this->normalizeName($name);
            } elseif ($named) {
                throw new Twix_Error_Syntax(sprintf('Positional arguments cannot be used after named arguments for %s "%s".', $callType, $callName));
            }
            $parameters[$name] = $node;
        }
        $isVariadic = $this->hasAttribute('is_variadic') && $this->getAttribute('is_variadic');
        if (!$named && !$isVariadic) {
            return $parameters;
        }
        if (!$callable) {
            if ($named) {
                $message = sprintf('Named arguments are not supported for %s "%s".', $callType, $callName);
            } else {
                $message = sprintf('Arbitrary positional arguments are not supported for %s "%s".', $callType, $callName);
            }
            throw new LogicException($message);
        }
        // manage named arguments
        $callableParameters = $this->getCallableParameters($callable, $isVariadic);
        $arguments = array();
        $names = array();
        $missingArguments = array();
        $optionalArguments = array();
        $pos = 0;
        foreach ($callableParameters as $callableParameter) {
            $names[] = $name = $this->normalizeName($callableParameter->name);
            if (array_key_exists($name, $parameters)) {
                if (array_key_exists($pos, $parameters)) {
                    throw new Twix_Error_Syntax(sprintf('Argument "%s" is defined twice for %s "%s".', $name, $callType, $callName));
                }
                if (!empty($missingArguments)) {
                    throw new Twix_Error_Syntax(sprintf(
                        'Argument "%s" could not be assigned for %s "%s(%s)" because it is mapped to an internal PHP function which cannot determine default value for optional argument%s "%s".',
                        $name, $callType, $callName, implode(', ', $names), count($missingArguments) > 1 ? 's' : '', implode('", "', $missingArguments))
                    );
                }
                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $parameters[$name];
                unset($parameters[$name]);
                $optionalArguments = array();
            } elseif (array_key_exists($pos, $parameters)) {
                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $parameters[$pos];
                unset($parameters[$pos]);
                $optionalArguments = array();
                ++$pos;
            } elseif ($callableParameter->isDefaultValueAvailable()) {
                $optionalArguments[] = new Twix_Node_Expression_Constant($callableParameter->getDefaultValue(), -1);
            } elseif ($callableParameter->isOptional()) {
                if (empty($parameters)) {
                    break;
                } else {
                    $missingArguments[] = $name;
                }
            } else {
                throw new Twix_Error_Syntax(sprintf('Value for argument "%s" is required for %s "%s".', $name, $callType, $callName));
            }
        }
        if ($isVariadic) {
            $arbitraryArguments = new Twix_Node_Expression_Array(array(), -1);
            foreach ($parameters as $key => $value) {
                if (is_int($key)) {
                    $arbitraryArguments->addElement($value);
                } else {
                    $arbitraryArguments->addElement($value, new Twix_Node_Expression_Constant($key, -1));
                }
                unset($parameters[$key]);
            }
            if ($arbitraryArguments->count()) {
                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $arbitraryArguments;
            }
        }
        if (!empty($parameters)) {
            $unknownParameter = null;
            foreach ($parameters as $parameter) {
                if ($parameter instanceof Twix_Node) {
                    $unknownParameter = $parameter;
                    break;
                }
            }
            throw new Twix_Error_Syntax(sprintf(
                'Unknown argument%s "%s" for %s "%s(%s)".',
                count($parameters) > 1 ? 's' : '', implode('", "', array_keys($parameters)), $callType, $callName, implode(', ', $names)
            ), $unknownParameter ? $unknownParameter->getLine() : -1);
        }
        return $arguments;
    }
    protected function normalizeName($name)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $name));
    }
    private function getCallableParameters($callable, $isVariadic)
    {
        if (is_array($callable)) {
            $r = new ReflectionMethod($callable[0], $callable[1]);
        } elseif (is_object($callable) && !$callable instanceof Closure) {
            $r = new ReflectionObject($callable);
            $r = $r->getMethod('__invoke');
        } elseif (is_string($callable) && false !== strpos($callable, '::')) {
            $r = new ReflectionMethod($callable);
        } else {
            $r = new ReflectionFunction($callable);
        }
        $parameters = $r->getParameters();
        if ($this->hasNode('node')) {
            array_shift($parameters);
        }
        if ($this->hasAttribute('needs_environment') && $this->getAttribute('needs_environment')) {
            array_shift($parameters);
        }
        if ($this->hasAttribute('needs_context') && $this->getAttribute('needs_context')) {
            array_shift($parameters);
        }
        if ($this->hasAttribute('arguments') && null !== $this->getAttribute('arguments')) {
            foreach ($this->getAttribute('arguments') as $argument) {
                array_shift($parameters);
            }
        }
        if ($isVariadic) {
            $argument = end($parameters);
            if ($argument && $argument->isArray() && $argument->isDefaultValueAvailable() && array() === $argument->getDefaultValue()) {
                array_pop($parameters);
            } else {
                $callableName = $r->name;
                if ($r->getDeclaringClass()) {
                    $callableName = $r->getDeclaringClass()->name.'::'.$callableName;
                }
                throw new LogicException(sprintf('The last parameter of "%s" for %s "%s" must be an array with default value, eg. "array $arg = array()".', $callableName, $this->getAttribute('type'), $this->getAttribute('name')));
            }
        }
        return $parameters;
    }
}

class Twix_Node_Expression_Filter extends Twix_Node_Expression_Call
{
    public function __construct(Twix_NodeInterface $node, Twix_Node_Expression_Constant $filterName, Twix_NodeInterface $arguments, $lineno, $tag = null)
    {
        parent::__construct(array('node' => $node, 'filter' => $filterName, 'arguments' => $arguments), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $name = $this->getNode('filter')->getAttribute('value');
        $filter = $compiler->getEnvironment()->getFilter($name);
        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'filter');
        $this->setAttribute('thing', $filter);
        $this->setAttribute('needs_environment', $filter->needsEnvironment());
        $this->setAttribute('needs_context', $filter->needsContext());
        $this->setAttribute('arguments', $filter->getArguments());
        if ($filter instanceof Twix_FilterCallableInterface || $filter instanceof Twix_SimpleFilter) {
            $this->setAttribute('callable', $filter->getCallable());
        }
        if ($filter instanceof Twix_SimpleFilter) {
            $this->setAttribute('is_variadic', $filter->isVariadic());
        }
        $this->compileCallable($compiler);
    }
}

abstract class Twix_Node_Expression_Unary extends Twix_Node_Expression
{
    public function __construct(Twix_NodeInterface $node, $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->raw(' ');
        $this->operator($compiler);
        $compiler->subcompile($this->getNode('node'));
    }
    abstract public function operator(Twix_Compiler $compiler);
}

class Twix_Node_Expression_Unary_Not extends Twix_Node_Expression_Unary
{
    public function operator(Twix_Compiler $compiler)
    {
        $compiler->raw('!');
    }
}

class Twix_Node_Expression_Binary_Equal extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('==');
    }
}

class Twix_Node_Expression_Binary_And extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('&&');
    }
}

class Twix_Node_Expression_Binary_Or extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('||');
    }
}

class Twix_Node_Expression_Binary_Div extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('/');
    }
}

class Twix_Node_Expression_Binary_LessEqual extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('<=');
    }
}

class Twix_Node_Expression_Binary_Range extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('range(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('..');
    }
}

class Twix_Node_Expression_Binary_In extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('twix_in_filter(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('in');
    }
}

class Twix_Node_Expression_Conditional extends Twix_Node_Expression
{
    public function __construct(Twix_Node_Expression $expr1, Twix_Node_Expression $expr2, Twix_Node_Expression $expr3, $lineno)
    {
        parent::__construct(array('expr1' => $expr1, 'expr2' => $expr2, 'expr3' => $expr3), array(), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(!empty(')
            ->subcompile($this->getNode('expr1'))
            ->raw(') ? (')
            ->subcompile($this->getNode('expr2'))
            ->raw(') : (')
            ->subcompile($this->getNode('expr3'))
            ->raw('))')
        ;
    }
}

class Twix_Node_Expression_Binary_GreaterEqual extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('>=');
    }
}

class Twix_Node_Expression_Binary_Mod extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('%');
    }
}

class Twix_Node_Expression_Binary_Sub extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('-');
    }
}



class Twix_Node_Expression_Function extends Twix_Node_Expression_Call
{
    public function __construct($name, Twix_NodeInterface $arguments, $lineno)
    {
        parent::__construct(array('arguments' => $arguments), array('name' => $name), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $function = $compiler->getEnvironment()->getFunction($name);
        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'function');
        $this->setAttribute('thing', $function);
        $this->setAttribute('needs_environment', $function->needsEnvironment());
        $this->setAttribute('needs_context', $function->needsContext());
        $this->setAttribute('arguments', $function->getArguments());
        if ($function instanceof Twix_FunctionCallableInterface || $function instanceof Twix_SimpleFunction) {
            $this->setAttribute('callable', $function->getCallable());
        }
        if ($function instanceof Twix_SimpleFunction) {
            $this->setAttribute('is_variadic', $function->isVariadic());
        }
        $this->compileCallable($compiler);
    }
}

class Twix_Node_Import extends Twix_Node
{
    public function __construct(Twix_Node_Expression $expr, Twix_Node_Expression $var, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'var' => $var), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->write('')
            ->subcompile($this->getNode('var'))
            ->raw(' = ')
        ;
        if ($this->getNode('expr') instanceof Twix_Node_Expression_Name && '_self' === $this->getNode('expr')->getAttribute('name')) {
            $compiler->raw('$this');
        } else {
            $compiler
                ->raw('$this->loadTemplate(')
                ->subcompile($this->getNode('expr'))
                ->raw(')') //MaxD
            ;
        }
        $compiler->raw(";\n");
    }
}

class Twix_Node_Macro extends Twix_Node
{
    const VARARGS_NAME = 'varargs';
    public function __construct($name, Twix_NodeInterface $body, Twix_NodeInterface $arguments, $lineno, $tag = null)
    {
        foreach ($arguments as $argumentName => $argument) {
            if (self::VARARGS_NAME === $argumentName) {
                throw new Twix_Error_Syntax(sprintf('The argument "%s" in macro "%s" cannot be defined because the variable "%s" is reserved for arbitrary arguments.', self::VARARGS_NAME, $name, self::VARARGS_NAME), $argument->getLine());
            }
        }
        parent::__construct(array('body' => $body, 'arguments' => $arguments), array('name' => $name), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->write(sprintf('public function %s(', $this->getAttribute('name')))
        ;
        $count = count($this->getNode('arguments'));
        $pos = 0;
        foreach ($this->getNode('arguments') as $name => $default) {
            $compiler
                ->raw('$__'.$name.'__ = ')
                ->subcompile($default)
            ;
            if (++$pos < $count) {
                $compiler->raw(', ');
            }
        }
        if (PHP_VERSION_ID >= 50600) {
            if ($count) {
                $compiler->raw(', ');
            }
            $compiler->raw('...$__varargs__');
        }
        $compiler
            ->raw(")\n")
            ->write("{\n")
            ->indent()
        ;
        $compiler
            ->write("\$context = \$this->env->mergeGlobals(array(\n")
            ->indent()
        ;
        foreach ($this->getNode('arguments') as $name => $default) {
            $compiler
                ->addIndentation()
                ->string($name)
                ->raw(' => $__'.$name.'__')
                ->raw(",\n")
            ;
        }
        $compiler
            ->addIndentation()
            ->string(self::VARARGS_NAME)
            ->raw(' => ')
        ;
        if (PHP_VERSION_ID >= 50600) {
            $compiler->raw("\$__varargs__,\n");
        } else {
            $compiler
                ->raw('func_num_args() > ')
                ->repr($count)
                ->raw(' ? array_slice(func_get_args(), ')
                ->repr($count)
                ->raw(") : array(),\n")
            ;
        }
        $compiler
            ->outdent()
            ->write("));\n\n")
            ->write("\$blocks = array();\n\n")
            ->write("if (!empty(\$context['context'])) extract(\$context['context']); extract(\$context);\n") //MX
            ->write("ob_start();\n")
            ->write("try {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("} catch (Exception \$e) {\n")
            ->indent()
            ->write("ob_end_clean();\n\n")
            ->write("throw \$e;\n")
            ->outdent()
            ->write("} catch (Throwable \$e) {\n")
            ->indent()
            ->write("ob_end_clean();\n\n")
            ->write("throw \$e;\n")
            ->outdent()
            ->write("}\n\n")
            ->write("return ('' === \$tmp = ob_get_clean()) ? '' : new Twix_Markup(\$tmp, \$this->env->getCharset());\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}

class Twix_Node_Expression_MethodCall extends Twix_Node_Expression
{
    public function __construct(Twix_Node_Expression $node, $method, Twix_Node_Expression_Array $arguments, $lineno)
    {
        parent::__construct(array('node' => $node, 'arguments' => $arguments), array('method' => $method, 'safe' => false), $lineno);
        if ($node instanceof Twix_Node_Expression_Name) {
            $node->setAttribute('always_defined', true);
        }
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->subcompile($this->getNode('node'))
            ->raw('->')
            ->raw($this->getAttribute('method'))
            ->raw('(')
        ;
        $first = true;
        foreach ($this->getNode('arguments')->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;
            $compiler->subcompile($pair['value']);
        }
        $compiler->raw(')');
    }
}

class Twix_Node_Expression_Test extends Twix_Node_Expression_Call
{
    public function __construct(Twix_NodeInterface $node, $name, Twix_NodeInterface $arguments = null, $lineno)
    {
        parent::__construct(array('node' => $node, 'arguments' => $arguments), array('name' => $name), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $test = $compiler->getEnvironment()->getTest($name);
        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'test');
        $this->setAttribute('thing', $test);
        if ($test instanceof Twix_TestCallableInterface || $test instanceof Twix_SimpleTest) {
            $this->setAttribute('callable', $test->getCallable());
        }
        if ($test instanceof Twix_SimpleTest) {
            $this->setAttribute('is_variadic', $test->isVariadic());
        }
        $this->compileCallable($compiler);
    }
}

class Twix_Node_Expression_Test_Null extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(null === ')
            ->subcompile($this->getNode('node'))
            ->raw(')')
        ;
    }
}

class Twix_Node_Include extends Twix_Node implements Twix_NodeOutputInterface
{
    public function __construct(Twix_Node_Expression $expr, Twix_Node_Expression $variables = null, $only = false, $ignoreMissing = false, $lineno = false, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'variables' => $variables), array('only' => (bool) $only, 'ignore_missing' => (bool) $ignoreMissing), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        if ($this->getAttribute('ignore_missing')) {
            $compiler
                ->write("try {\n")
                ->indent()
            ;
        }
        $this->addGetTemplate($compiler);
        $compiler->raw('->display(');
        $this->addTemplateArguments($compiler);
        $compiler->raw(");\n");
        if ($this->getAttribute('ignore_missing')) {
            $compiler
                ->outdent()
                ->write("} catch (Twix_Error_Loader \$e) {\n")
                ->indent()
                ->write("// ignore missing template\n")
                ->outdent()
                ->write("}\n\n")
            ;
        }
    }
    protected function addGetTemplate(Twix_Compiler $compiler)
    {
        $compiler
             ->write('$this->loadTemplate(')
             ->subcompile($this->getNode('expr'))
             ->raw(')') //MaxD
         ;
    }
    protected function addTemplateArguments(Twix_Compiler $compiler)
    {
        if (null === $this->getNode('variables')) {
            $compiler->raw(false === $this->getAttribute('only') ? 'get_defined_vars()' : 'array()'); //MaxD
        } elseif (false === $this->getAttribute('only')) {
            $compiler
                ->raw('array_merge(get_defined_vars(), ') //MaxD
                ->subcompile($this->getNode('variables'))
                ->raw(')')
            ;
        } else {
            $compiler->subcompile($this->getNode('variables'));
        }
    }
}

class Twix_Node_Expression_Binary_Concat extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('.');
    }
}

class Twix_Node_Expression_Binary_NotEqual extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('!=');
    }
}

class Twix_Node_Expression_Test_Defined extends Twix_Node_Expression_Test
{
    public function __construct(Twix_NodeInterface $node, $name, Twix_NodeInterface $arguments = null, $lineno)
    {
        if ($node instanceof Twix_Node_Expression_Name) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof Twix_Node_Expression_GetAttr) {
            $node->setAttribute('is_defined_test', true);
            $this->changeIgnoreStrictCheck($node);
        } elseif ($node instanceof Twix_Node_Expression_Constant || $node instanceof Twix_Node_Expression_Array) {
            $node = new Twix_Node_Expression_Constant(true, $node->getLine());
        } else {
            throw new Twix_Error_Syntax('The "defined" test only works with simple variables.', $this->getLine());
        }
        parent::__construct($node, $name, $arguments, $lineno);
    }
    protected function changeIgnoreStrictCheck(Twix_Node_Expression_GetAttr $node)
    {
        $node->setAttribute('ignore_strict_check', true);
        if ($node->getNode('node') instanceof Twix_Node_Expression_GetAttr) {
            $this->changeIgnoreStrictCheck($node->getNode('node'));
        }
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}

class Twix_Node_Expression_Filter_Default extends Twix_Node_Expression_Filter
{
    public function __construct(Twix_NodeInterface $node, Twix_Node_Expression_Constant $filterName, Twix_NodeInterface $arguments, $lineno, $tag = null)
    {
        $default = new Twix_Node_Expression_Filter($node, new Twix_Node_Expression_Constant('default', $node->getLine()), $arguments, $node->getLine());
        if ('default' === $filterName->getAttribute('value') && ($node instanceof Twix_Node_Expression_Name || $node instanceof Twix_Node_Expression_GetAttr)) {
            $test = new Twix_Node_Expression_Test_Defined(clone $node, 'defined', new Twix_Node(), $node->getLine());
            $false = count($arguments) ? $arguments->getNode(0) : new Twix_Node_Expression_Constant('', $node->getLine());
            $node = new Twix_Node_Expression_Conditional($test, $default, $false, $node->getLine());
        } else {
            $node = $default;
        }
        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}

class Twix_Node_Expression_Binary_Matches extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('preg_match(')
            ->subcompile($this->getNode('right'))
            ->raw(', ')
            ->subcompile($this->getNode('left'))
            ->raw(')')
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}

class Twix_Node_Expression_Unary_Neg extends Twix_Node_Expression_Unary
{
    public function operator(Twix_Compiler $compiler)
    {
        $compiler->raw('-');
    }
}

class Twix_Node_Expression_NullCoalesce extends Twix_Node_Expression_Conditional
{
    public function __construct(Twix_NodeInterface $left, Twix_NodeInterface $right, $lineno)
    {
        $test = new Twix_Node_Expression_Binary_And(
            new Twix_Node_Expression_Test_Defined(clone $left, 'defined', new Twix_Node(), $left->getLine()),
            new Twix_Node_Expression_Unary_Not(new Twix_Node_Expression_Test_Null($left, 'null', new Twix_Node(), $left->getLine()), $left->getLine()),
            $left->getLine()
        );

        parent::__construct($test, $left, $right, $lineno);
    }
}
class Twix_Node_AutoEscape extends Twix_Node
{
    public function __construct($value, Twix_NodeInterface $body, $lineno, $tag = 'autoescape')
    {
        parent::__construct(array('body' => $body), array('value' => $value), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('body'));
    }
}

class Twix_Node_Block extends Twix_Node
{
    public function __construct($name, Twix_NodeInterface $body, $lineno, $tag = null)
    {
        parent::__construct(array('body' => $body), array('name' => $name), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("public function block_%s(\$context, array \$blocks = array())\n", $this->getAttribute('name')), "{\n")
            ->indent()
        ;
        $compiler
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("}\n\n")
        ;
    }
}

class Twix_Node_BlockReference extends Twix_Node implements Twix_NodeOutputInterface
{
    public function __construct($name, $lineno, $tag = null)
    {
        parent::__construct(array(), array('name' => $name), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("\$this->displayBlock('%s', \$context, \$blocks);\n", $this->getAttribute('name')))
        ;
    }
}

class Twix_Node_CheckSecurity extends Twix_Node
{
    protected $usedFilters;
    protected $usedTags;
    protected $usedFunctions;
    public function __construct(array $usedFilters, array $usedTags, array $usedFunctions)
    {
        $this->usedFilters = $usedFilters;
        $this->usedTags = $usedTags;
        $this->usedFunctions = $usedFunctions;
        parent::__construct();
    }
    public function compile(Twix_Compiler $compiler)
    {
        $tags = $filters = $functions = array();
        foreach (array('tags', 'filters', 'functions') as $type) {
            foreach ($this->{'used'.ucfirst($type)} as $name => $node) {
                if ($node instanceof Twix_Node) {
                    ${$type}[$name] = $node->getLine();
                } else {
                    ${$type}[$node] = null;
                }
            }
        }
        $compiler
            ->write('$tags = ')->repr(array_filter($tags))->raw(";\n")
            ->write('$filters = ')->repr(array_filter($filters))->raw(";\n")
            ->write('$functions = ')->repr(array_filter($functions))->raw(";\n\n")
            ->write("try {\n")
            ->indent()
            ->write("\$this->env->getExtension('sandbox')->checkSecurity(\n")
            ->indent()
            ->write(!$tags ? "array(),\n" : "array('".implode("', '", array_keys($tags))."'),\n")
            ->write(!$filters ? "array(),\n" : "array('".implode("', '", array_keys($filters))."'),\n")
            ->write(!$functions ? "array()\n" : "array('".implode("', '", array_keys($functions))."')\n")
            ->outdent()
            ->write(");\n")
            ->outdent()
            ->write("} catch (Twix_Sandbox_SecurityError \$e) {\n")
            ->indent()
            ->write("\$e->setTemplateFile(\$this->getTemplateName());\n\n")
            ->write("if (\$e instanceof Twix_Sandbox_SecurityNotAllowedTagError && isset(\$tags[\$e->getTagName()])) {\n")
            ->indent()
            ->write("\$e->setTemplateLine(\$tags[\$e->getTagName()]);\n")
            ->outdent()
            ->write("} elseif (\$e instanceof Twix_Sandbox_SecurityNotAllowedFilterError && isset(\$filters[\$e->getFilterName()])) {\n")
            ->indent()
            ->write("\$e->setTemplateLine(\$filters[\$e->getFilterName()]);\n")
            ->outdent()
            ->write("} elseif (\$e instanceof Twix_Sandbox_SecurityNotAllowedFunctionError && isset(\$functions[\$e->getFunctionName()])) {\n")
            ->indent()
            ->write("\$e->setTemplateLine(\$functions[\$e->getFunctionName()]);\n")
            ->outdent()
            ->write("}\n\n")
            ->write("throw \$e;\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}

class Twix_Node_Do extends Twix_Node
{
    public function __construct(Twix_Node_Expression $expr, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
    }
}

class Twix_Node_Embed extends Twix_Node_Include
{
    // we don't inject the module to avoid node visitors to traverse it twice (as it will be already visited in the main module)
    public function __construct($filename, $index, Twix_Node_Expression $variables = null, $only = false, $ignoreMissing = false, $lineno = false, $tag = null)
    {
        parent::__construct(new Twix_Node_Expression_Constant('not_used', $lineno), $variables, $only, $ignoreMissing, $lineno, $tag);
        $this->setAttribute('filename', $filename);
        $this->setAttribute('index', $index);
    }
    protected function addGetTemplate(Twix_Compiler $compiler)
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->string($this->getAttribute('filename'))
            ->raw(', ')
            ->repr($compiler->getFilename())
            ->raw(', ')
            ->repr($this->getLine())
            ->raw(', ')
            ->string($this->getAttribute('index'))
            ->raw(')')
        ;
    }
}

class Twix_Node_Flush extends Twix_Node
{
    public function __construct($lineno, $tag)
    {
        parent::__construct(array(), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("flush();\n")
        ;
    }
}

class Twix_Node_Sandbox extends Twix_Node
{
    public function __construct(Twix_NodeInterface $body, $lineno, $tag = null)
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$sandbox = \$this->env->getExtension('sandbox');\n")
            ->write("if (!\$alreadySandboxed = \$sandbox->isSandboxed()) {\n")
            ->indent()
            ->write("\$sandbox->enableSandbox();\n")
            ->outdent()
            ->write("}\n")
            ->subcompile($this->getNode('body'))
            ->write("if (!\$alreadySandboxed) {\n")
            ->indent()
            ->write("\$sandbox->disableSandbox();\n")
            ->outdent()
            ->write("}\n")
        ;
    }
}

class Twix_Node_SandboxedPrint extends Twix_Node_Print
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('echo $this->env->getExtension(\'sandbox\')->ensureToStringAllowed(')
            ->subcompile($this->getNode('expr'))
            ->raw(");\n")
        ;
    }
    
    protected function removeNodeFilter($node)
    {
        if ($node instanceof Twix_Node_Expression_Filter) {
            return $this->removeNodeFilter($node->getNode('node'));
        }
        return $node;
    }
}

class Twix_Node_SetTemp extends Twix_Node
{
    public function __construct($name, $lineno)
    {
        parent::__construct(array(), array('name' => $name), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $compiler
            ->addDebugInfo($this)
            ->write('if (isset($context[')
            ->string($name)
            ->raw('])) { $_')
            ->raw($name)
            ->raw('_ = $context[')
            ->repr($name)
            ->raw(']; } else { $_')
            ->raw($name)
            ->raw("_ = null; }\n")
        ;
    }
}

class Twix_Node_Spaceless extends Twix_Node
{
    public function __construct(Twix_NodeInterface $body, $lineno, $tag = 'spaceless')
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));\n")
        ;
    }
}

class Twix_Node_Expression_Binary_BitwiseAnd extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('&');
    }
}

class Twix_Node_Expression_Binary_BitwiseOr extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('|');
    }
}

class Twix_Node_Expression_Binary_BitwiseXor extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('^');
    }
}

class Twix_Node_Expression_Binary_EndsWith extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $left = $compiler->getVarName();
        $right = $compiler->getVarName();
        $compiler
            ->raw(sprintf('(is_string($%s = ', $left))
            ->subcompile($this->getNode('left'))
            ->raw(sprintf(') && is_string($%s = ', $right))
            ->subcompile($this->getNode('right'))
            ->raw(sprintf(') && (\'\' === $%2$s || $%2$s === substr($%1$s, -strlen($%2$s))))', $left, $right))
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}

class Twix_Node_Expression_Binary_FloorDiv extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler->raw('intval(floor(');
        parent::compile($compiler);
        $compiler->raw('))');
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('/');
    }
}

class Twix_Node_Expression_Binary_Mul extends Twix_Node_Expression_Binary
{
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('*');
    }
}

class Twix_Node_Expression_Binary_NotIn extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('!twix_in_filter(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('not in');
    }
}

class Twix_Node_Expression_Binary_Power extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('pow(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('**');
    }
}

class Twix_Node_Expression_Binary_StartsWith extends Twix_Node_Expression_Binary
{
    public function compile(Twix_Compiler $compiler)
    {
        $left = $compiler->getVarName();
        $right = $compiler->getVarName();
        $compiler
            ->raw(sprintf('(is_string($%s = ', $left))
            ->subcompile($this->getNode('left'))
            ->raw(sprintf(') && is_string($%s = ', $right))
            ->subcompile($this->getNode('right'))
            ->raw(sprintf(') && (\'\' === $%2$s || 0 === strpos($%1$s, $%2$s)))', $left, $right))
        ;
    }
    public function operator(Twix_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}

class Twix_Node_Expression_BlockReference extends Twix_Node_Expression
{
    public function __construct(Twix_NodeInterface $name, $asString = false, $lineno = false, $tag = null)
    {
        parent::__construct(array('name' => $name), array('as_string' => $asString, 'output' => false), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        if ($this->getAttribute('as_string')) {
            $compiler->raw('(string) ');
        }
        if ($this->getAttribute('output')) {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->displayBlock(')
                ->subcompile($this->getNode('name'))
                ->raw(", \$context, \$blocks);\n")
            ;
        } else {
            $compiler
                ->raw('$this->renderBlock(')
                ->subcompile($this->getNode('name'))
                ->raw(', $context, $blocks)')
            ;
        }
    }
}

class Twix_Node_Expression_Parent extends Twix_Node_Expression
{
    public function __construct($name, $lineno, $tag = null)
    {
        parent::__construct(array(), array('output' => false, 'name' => $name), $lineno, $tag);
    }
    public function compile(Twix_Compiler $compiler)
    {
        if ($this->getAttribute('output')) {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->displayParentBlock(')
                ->string($this->getAttribute('name'))
                ->raw(", \$context, \$blocks);\n")
            ;
        } else {
            $compiler
                ->raw('$this->renderParentBlock(')
                ->string($this->getAttribute('name'))
                ->raw(', $context, $blocks)')
            ;
        }
    }
}

class Twix_Node_Expression_TempName extends Twix_Node_Expression
{
    public function __construct($name, $lineno)
    {
        parent::__construct(array(), array('name' => $name), $lineno);
    }
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('$_')
            ->raw($this->getAttribute('name'))
            ->raw('_')
        ;
    }
}

class Twix_Node_Expression_Test_Constant extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' === constant(')
        ;
        if ($this->getNode('arguments')->hasNode(1)) {
            $compiler
                ->raw('get_class(')
                ->subcompile($this->getNode('arguments')->getNode(1))
                ->raw(')."::".')
            ;
        }
        $compiler
            ->subcompile($this->getNode('arguments')->getNode(0))
            ->raw('))')
        ;
    }
}

class Twix_Node_Expression_Test_Divisibleby extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(0 == ')
            ->subcompile($this->getNode('node'))
            ->raw(' % ')
            ->subcompile($this->getNode('arguments')->getNode(0))
            ->raw(')')
        ;
    }
}

class Twix_Node_Expression_Test_Even extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' % 2 == 0')
            ->raw(')')
        ;
    }
}

class Twix_Node_Expression_Test_Odd extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' % 2 == 1')
            ->raw(')')
        ;
    }
}

class Twix_Node_Expression_Test_Sameas extends Twix_Node_Expression_Test
{
    public function compile(Twix_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' === ')
            ->subcompile($this->getNode('arguments')->getNode(0))
            ->raw(')')
        ;
    }
}

class Twix_Node_Expression_Unary_Pos extends Twix_Node_Expression_Unary
{
    public function operator(Twix_Compiler $compiler)
    {
        $compiler->raw('+');
    }
}
