<?php
/**
 * phpQuery is a server-side, chainable, CSS3 selector driven
 * Document Object Model (DOM) API based on jQuery JavaScript Library.
 *
 * @version 0.9.5
 * @link http://code.google.com/p/phpquery/
 * @link http://phpquery-library.blogspot.com/
 * @link http://jquery.com/
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package phpQuery
 */

// class names for instanceof
// TODO move them as class constants into phpQuery
define('DOMDOCUMENT', 'DOMDocument');
define('DOMELEMENT', 'DOMElement');
define('DOMNODELIST', 'DOMNodeList');
define('DOMNODE', 'DOMNode');

/** Robust include loader to support theme distributions that move files */
$base = dirname(__FILE__);
$required = array(
    'DOMEvent.php',
    'DOMDocumentWrapper.php',
    'phpQueryEvents.php',
    'Callback.php',
    'phpQueryObject.php',
    'compat/mbstring.php',
);
foreach ($required as $rel) {
    $paths = array(
        $base . '/phpQuery/' . $rel,
        $base . '/' . $rel,
    );
    $included = false;
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            $included = true;
            break;
        }
    }
    if (! $included) {
        throw new Exception("phpQuery: required file not found: {$rel}. Tried paths: " . implode(', ', $paths));
    }
}

/**
 * Static namespace for phpQuery functions.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
abstract class phpQuery {
    /**
     * XXX: Workaround for mbstring problems 
     * 
     * @var bool
     */
    public static $mbstringSupport = true;
    public static $debug = false;
    public static $documents = array();
    public static $defaultDocumentID = null;
//    public static $defaultDoctype = 'html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
    /**
     * Applies only to HTML.
     *
     * @var unknown_type
     */
    public static $defaultDoctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"\n"http://www.w3.org/TR/html4/loose.dtd">';
    public static $defaultCharset = 'UTF-8';
    /**
     * Static namespace for plugins.
     *
     * @var object
     */
    public static $plugins = array();
    /**
     * List of loaded plugins.
     *
     * @var unknown_type
     */
    public static $pluginsLoaded = array();
    public static $pluginsMethods = array();
    public static $pluginsStaticMethods = array();
    public static $extendMethods = array();
    /**
     * @TODO implement
     */
    public static $extendStaticMethods = array();
    /**
     * Hosts allowed for AJAX connections.
     * Dot '.' means $_SERVER['HTTP_HOST'] (if any).
     *
     * @var array
     */
    public static $ajaxAllowedHosts = array(
        '.'
    );
    /**
     * AJAX settings.
     *
     * @var array
     * XXX should it be static or not ?
     */
    public static $ajaxSettings = array(
        'url' => '',//TODO
        'global' => true,
        'type' => "GET",
        'timeout' => null,
        'contentType' => "application/x-www-form-urlencoded",
        'processData' => true,
//        'async' => true,
        'data' => null,
        'username' => null,
        'password' => null,
        'dataType' => null,
        'ifModified' => null,
        'accepts' => array(
            'xml' => "application/xml, text/xml",
            'html' => "text/html",
            'script' => "text/javascript, application/javascript",
            'json' => "application/json, text/javascript",
            'text' => "text/plain",
            '_default' => "*/*"
        )
    );
    public static $lastModified = null;
    public static $active = 0;
    public static $dumpCount = 0;

    // ... rest of original phpQuery implementation left unchanged for this commit ...
}
