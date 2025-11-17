<?php
/**
 * Class representing phpQuery objects.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @method phpQueryObject clone() clone()
 * @method phpQueryObject empty() empty()
 * @method phpQueryObject next() next($selector = null)
 * @method phpQueryObject prev() prev($selector = null)
 * @property Int $length
 */
class phpQueryObject
 implements Iterator, Countable, ArrayAccess {
 public $documentID = null;
 /**
  * DOMDocument class.
  *
  * @var DOMDocument
  */
 public $document = null;
 public $charset = null;
 /**
  *
  * @var DOMDocumentWrapper
  */
 public $documentWrapper = null;
 /**
  * XPath interface.
  *
  * @var DOMXPath
  */
 public $xpath = null;
 /**
  * Stack of selected elements.
  * @TODO refactor to ->nodes
  * @var array
  */
 public $elements = array();
 /**
  * @access private
  */
 protected $elementsBackup = array();
 /**
  * @access private
  */
 protected $previous = null;
 /**
  * @access private
  * @TODO deprecate
  */
 protected $root = array();
 /**
  * Indicated if doument is just a fragment (no <html> tag).
  *
  * Every document is realy a full document, so even documentFragments can
  * be queried against <html>, but getDocument(id)->htmlOuter() will return
  * only contents of <body>.
  *
  * @var bool
  */
 public $documentFragment = true;
 /**
  * Iterator interface helper
  * @access private
  */
 protected $elementsInterator = array();
 /**
  * Iterator interface helper
  * @access private
  */
 protected $valid = false;
 /**
  * Iterator interface helper
  * @access private
  */
 protected $current = null;
 /**
  * Enter description here...
  *
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function __construct($documentID) {
 //		if ($documentID instanceof self)
 //			var_dump($documentID->getDocumentID());
  $id = $documentID instanceof self
   ? $documentID->getDocumentID()
   : $documentID;
 //		var_dump($id);
  if (! isset(phpQuery::$documents[$id] )) {
 //			var_dump(phpQuery::$documents);
   throw new Exception("Document with ID '{".$id."}' isn't loaded. Use phpQuery::newDocument(\$html) or phpQuery::newDocumentFile(\$file) first.");
  }
  $this->documentID = $id;
  $this->documentWrapper =& phpQuery::$documents[$id];
  $this->document =& $this->documentWrapper->document;
  $this->xpath =& $this->documentWrapper->xpath;
  $this->charset =& $this->documentWrapper->charset;
  $this->documentFragment =& $this->documentWrapper->isDocumentFragment;
  // TODO check $this->DOM->documentElement;
 //		$this->root = $this->document->documentElement;
  $this->root =& $this->documentWrapper->root;
 //		$this->toRoot();
  $this->elements = array($this->root);
 }
 /**
  *
  * @access private
  * @param $attr
  * @return unknown_type
  */
 public function __get($attr) {
  switch($attr) {
   // FIXME doesnt work at all ?
   case 'length':
    return $this->size();
   break;
   default:
    return $this->$attr;
  }
 }
 /**
  * Saves actual object to $var by reference.
  * Useful when need to break chain.
  * @param phpQueryObject $var
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function toReference(&$var) {
  return $var = $this;
 }
 public function documentFragment($state = null) {
  if ($state) {
   phpQuery::$documents[$this->getDocumentID()]['documentFragment'] = $state;
   return $this;
  }
  return $this->documentFragment;
 }
 /**
    * @access private
    * @TODO documentWrapper
     */
 protected function isRoot( $node) {
 //		return $node instanceof DOMDOCUMENT || $node->tagName == 'html';
  return $node instanceof DOMDOCUMENT
   || ($node instanceof DOMELEMENT && $node->tagName == 'html')
   || $this->root->isSameNode($node);
 }
 /**
    * @access private
     */
 protected function stackIsRoot() {
  return $this->size() == 1 && $this->isRoot($this->elements[0]);
 }
 /**
  * Enter description here...
  * NON JQUERY METHOD
  *
  * Watch out, it doesn't creates new instance, can be reverted with end().
  *
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function toRoot() {
  $this->elements = array($this->root);
  return $this;
 //		return $this->newInstance(array($this->root));
 }
 /**
  * Saves object's DocumentID to $var by reference.
  * <code>
  * $myDocumentId;
  * phpQuery::newDocument('<div/>')
  *     ->getDocumentIDRef($myDocumentId)
  *     ->find('div')->...
  * </code>
  *
  * @param unknown_type $domId
  * @see phpQuery::newDocument
  * @see phpQuery::newDocumentFile
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function getDocumentIDRef(&$documentID) {
  $documentID = $this->getDocumentID();
  return $this;
 }
 /**
  * Returns object with stack set to document root.
  ***
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function getDocument() {
  return phpQuery::getDocument($this->getDocumentID());
 }
 /**
  *
  * @return DOMDocument
  */
 public function getDOMDocument() {
  return $this->document;
 }
 /**
  * Get object's Document ID.
  *
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function getDocumentID() {
  return $this->documentID;
 }
 /**
  * Unloads whole document from memory.
  * CAUTION! None further operations will be possible on this document.
  * All objects refering to it will be useless.
  *
  * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
  */
 public function unloadDocument() {
  phpQuery::unloadDocuments($this->getDocumentID());
 }
 public function isHTML() {
  return $this->documentWrapper->isHTML;
 }
 public function isXHTML() {
  return $this->documentWrapper->isXHTML;
 }
 public function isXML() {
  return $this->documentWrapper->isXML;
 }
 /**
  * Enter description here...
  *
  * @link http://docs.jquery.com/Ajax/serialize
  * @return string
  */
 public function serialize() {
  return phpQuery::param($this->serializeArray());
 }
 /**
  * Enter description here...
  *
  * @link http://docs.jquery.com/Ajax/serializeArray
  * @return array
  */
 public function serializeArray($submit = null) {
  $source = $this->filter('form, input, select, textarea')
   ->find('input, select, textarea')
   ->andSelf()
   ->not('form');
  $return = array();
 //		$source->dumpDie();
  foreach($source as $input) {
   $input = phpQuery::pq($input);
   if ($input->is('[disabled]'))
    continue;
   if (!$input->is('[name]'))
    continue;
   if ($input->is('[type=checkbox]') && !$input->is('[checked]'))
    continue;
   // jquery diff
   if ($submit && $input->is('[type=submit]')) {
    if ($submit instanceof DOMELEMENT && ! $input->elements[0]->isSameNode($submit))
     continue;
    else if (is_string($submit) && $input->attr('name') != $submit)
     continue;
   }
   $return[] = array(
    'name' => $input->attr('name'),
    'value' => $input->val(),
   );
  }
  return $return;
 }
 /**
  * @access private
  */
 protected function debug($in) {
  if (! phpQuery::$debug )
   return;
  print('<pre>');
  print_r($in);
  // file debug
 //		file_put_contents(dirname(__FILE__).'/phpQuery.log', print_r($in, true)."
", FILE_APPEND);
  // quite handy debug trace
 //		if ( is_array($in))
 //			print_r(array_slice(debug_backtrace(), 3));
  print("</pre>\n");
 }
 /**
  * @access private
  */
 protected function isRegexp($pattern) {
  return in_array(
   $pattern[ mb_strlen($pattern)-1 ],
   array('^','*','$')
  );
 }
 /**
  * Determines if $char is really a char.
  *
  * @param string $char
  * @return bool
  * @todo rewrite me to charcode range ! ;)
  * @access private
  */
 protected function isChar($char) {
  return extension_loaded('mbstring') && phpQuery::$mbstringSupport
   ? mb_eregi('\w', $char)
   : preg_match('@\w@', $char);
 }
 /**
  * @access private
  */
 protected function parseSelector($query) {
  // clean spaces
  // TODO include this inside parsing ?
  $query = trim(
   preg_replace('@\s+@', ' ',
    preg_replace('@\s*(>|\\+|~)\s*@', '\1', $query)
   )
  );
  $queries = array(array());
  if (! $query)
   return $queries;
  $return =& $queries[0];
  $specialChars = array('>',' ');
 //		$specialCharsMapping = array('/' => '>');
  $specialCharsMapping = array();
  $strlen = mb_strlen($query);
  $classChars = array('.', '-');
  $pseudoChars = array('-');
  $tagChars = array('*', '|', '-');
  // split multibyte string
  // http://code.google.com/p/phpquery/issues/detail?id=76
  $_query = array();
  for ($i=0; $i<$strlen; $i++)
   $_query[] = mb_substr($query, $i, 1);
  $query = $_query;
  // it works, but i dont like it...
  $i = 0;
  while( $i < $strlen) {
   $c = $query[$i];
   $tmp = '';
   // TAG
   if ($this->isChar($c) || in_array($c, $tagChars)) {
    while(isset($query[$i])
     && ($this->isChar($query[$i]) || in_array($query[$i], $tagChars))) {
     $tmp .= $query[$i];
     $i++;
    }
    $return[] = $tmp;
   // IDs
   } else if ( $c == '#') {
    $i++;
    while( isset($query[$i]) && ($this->isChar($query[$i]) || $query[$i] == '-')) {
     $tmp .= $query[$i];
     $i++;
    }
    $return[] = '#'.$tmp;
   // SPECIAL CHARS
   } else if (in_array($c, $specialChars)) {
    $return[] = $c;
    $i++;
   // MAPPED SPECIAL MULTICHARS
 }
 }