<?php

namespace voku\helper;

/**
 * Class HtmlMin
 *
 * Inspired by:
 * - JS: https://github.com/kangax/html-minifier/blob/gh-pages/src/htmlminifier.js
 * - PHP: https://github.com/searchturbine/phpwee-php-minifier
 * - PHP: https://github.com/WyriHaximus/HtmlCompress
 * - PHP: https://github.com/zaininnari/html-minifier
 * - Java: https://code.google.com/archive/p/htmlcompressor/
 *
 * @package voku\helper
 */
class HtmlMin
{
  /**
   * // https://mathiasbynens.be/demo/javascript-mime-type
   * // https://developer.mozilla.org/en/docs/Web/HTML/Element/script#attr-type
   *
   * @var array
   */
  private static $executableScriptsMimeTypes = array(
      'text/javascript',
      'text/ecmascript',
      'text/jscript',
      'application/javascript',
      'application/x-javascript',
      'application/ecmascript',
  );

  /**
   * @var array
   */
  private static $booleanAttributes = array(
      'allowfullscreen',
      'async',
      'autofocus',
      'autoplay',
      'checked',
      'compact',
      'controls',
      'declare',
      'default',
      'defaultchecked',
      'defaultmuted',
      'defaultselected',
      'defer',
      'disabled',
      'enabled',
      'formnovalidate',
      'hidden',
      'indeterminate',
      'inert',
      'ismap',
      'itemscope',
      'loop',
      'multiple',
      'muted',
      'nohref',
      'noresize',
      'noshade',
      'novalidate',
      'nowrap',
      'open',
      'pauseonexit',
      'readonly',
      'required',
      'reversed',
      'scoped',
      'seamless',
      'selected',
      'sortable',
      'truespeed',
      'typemustmatch',
      'visible',
  );

  /**
   * An random md5-hash, generated via "random_bytes()".
   *
   * @var string
   */
  private $randomHash;

  /**
   * @var array
   */
  private $protectedChildNodes;

  /**
   * @var array
   */
  private static $skipTagsForRemoveWhitespace = array('style', 'pre', 'code', 'script', 'textarea');

  /**
   * @var string
   */
  private $protectedChildNodesHelper;

  /**
   * @var string
   */
  private $booleanAttributesHelper;

  /**
   * HtmlMin constructor.
   */
  public function __construct()
  {
    $this->protectedChildNodes = array();
    $this->randomHash = md5(Bootup::get_random_bytes(16));

    $this->protectedChildNodesHelper = 'html-min--saved-content-' . $this->randomHash;
    $this->booleanAttributesHelper = 'html-min--delete-this-' . $this->randomHash;
  }

  /**
   * @param string $html
   *
   * @return string
   */
  public function minify($html)
  {
    $html = (string)$html;
    if (!isset($html[0])) {
      return '';
    }

    $html = trim($html);
    if (!$html) {
      return '';
    }

    // init
    $this->protectedChildNodes = array();
    $origHtml = $html;
    $origHtmlLength = UTF8::strlen($html);

    $dom = new HtmlDomParser();
    $dom->getDocument()->preserveWhiteSpace = false; // remove redundant white space
    $dom->getDocument()->formatOutput = false; // do not formats output with indentation

    $dom->loadHtml($html);

    $dom = $this->protectTagsInDom($dom);
    $dom = $this->optimizeAttributesInDom($dom);
    $dom = $this->removeCommentsInDom($dom);
    $dom = $this->removeWhitespaceInDom($dom);
    $dom = $this->trimTagsInDom($dom);

    $html = $dom->html();

    // -------------------------------------------------------------------------
    // Trim whitespace from html-string. [protected html is still protected]
    // -------------------------------------------------------------------------

    // Remove spaces that are followed by either > or <
    $html = preg_replace('/ (>)/', '$1', $html);
    // Remove spaces that are preceded by either > or <
    $html = preg_replace('/(<) /', '$1', $html);
    // Remove spaces that are between > and <
    $html = preg_replace('/(>) (<)/', '>$2', $html);

    // -------------------------------------------------------------------------
    // Restore protected HTML-code.
    // -------------------------------------------------------------------------

    $html = preg_replace_callback(
        '/<(?<element>'. $this->protectedChildNodesHelper . ')(?<attributes> [^>]*)?>(?<value>.*?)<\/' . $this->protectedChildNodesHelper . '>/',
        array($this, 'restoreProtectedHtml'),
        $html
    );
    $html = $dom::putReplacedBackToPreserveHtmlEntities($html);

    // ------------------------------------
    // final clean-up
    // ------------------------------------

    $html = UTF8::cleanup($html);

    $html = str_replace(
        array(
            'html>' . "\n",
            "\n" . '<html',
            '<!doctype',
            '="' . $this->booleanAttributesHelper . '"',
            '</' . $this->protectedChildNodesHelper . '>',
        ),
        array(
            'html>',
            '<html',
            '<!DOCTYPE',
            '',
            '',
        ),
        $html
    );

    $html = preg_replace(
        array(
            '/<(?:' . $this->protectedChildNodesHelper . ')(:? [^>]*)?>/'
        ),
        array(
            ''
        ),
        $html
    );

    // ------------------------------------
    // check if compression worked
    // ------------------------------------

    if ($origHtmlLength < UTF8::strlen($html)) {
      $html = $origHtml;
    }

    return $html;
  }

  /**
   * Prevent changes of inline "styles" and "scripts".
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function protectTagsInDom(HtmlDomParser $dom)
  {
    // init
    $i = 0;

    foreach ($dom->find('script, style') as $element) {

      // skip external links
      if ($element->tag === 'script' || $element->tag === 'style') {
        $attributs = $element->getAllAttributes();
        if (isset($attributs['src'])) {
          continue;
        }
      }

      $node = $element->getNode();
      while ($node->childNodes->length > 0) {
        $this->protectedChildNodes[$i][] = $node->firstChild->nodeValue;
        $node->removeChild($node->firstChild);
      }

      $child = new \DOMElement($this->protectedChildNodesHelper);
      $node = $element->getNode()->appendChild($child);
      /* @var $node \DOMElement */
      $node->setAttribute('data-html-min--saved-content', $i);

      ++$i;
    }

    return $dom;
  }

  /**
   * Optimize HTML-tag attributes in the dom.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function optimizeAttributesInDom(HtmlDomParser $dom)
  {
    foreach ($dom->find('*') as $element) {
      $attributs = $element->getAllAttributes();

      $this->optimizeAttributes($element, $attributs);
    }

    return $dom;
  }

  /**
   * Remove comments in the dom.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function removeCommentsInDom(HtmlDomParser $dom)
  {
    foreach ($dom->find('//comment()') as $commentWrapper) {
      $comment = $commentWrapper->getNode();
      $val = $comment->nodeValue;
      if (strpos($val, '[') !== 0) {
        $comment->parentNode->removeChild($comment);
      }
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }

  /**
   * Trim tags in the dom.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function trimTagsInDom(HtmlDomParser $dom) {
    $divnodes = $dom->find('//div|//p|//nav|//footer|//article|//script|//hr|//br');
    foreach ($divnodes as $divnodeWrapper) {
      $divnode = $divnodeWrapper->getNode();

      $candidates = array();
      /** @noinspection PhpParamsInspection */
      if (count($divnode->childNodes) > 0) {
        $candidates[] = $divnode->firstChild;
        $candidates[] = $divnode->lastChild;
        $candidates[] = $divnode->previousSibling;
        $candidates[] = $divnode->nextSibling;
      }

      foreach ($candidates as $candidate) {
        if ($candidate === null) {
          continue;
        }

        if ($candidate->nodeType === 3) {
          $candidate->nodeValue = trim($candidate->nodeValue);
        }
      }
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }

  /**
   * Remove whitespace from dom-nodes.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function removeWhitespaceInDom(HtmlDomParser $dom)
  {
    $textnodes = $dom->find('//text()');
    foreach ($textnodes as $textnodeWrapper) {
      $textnode = $textnodeWrapper->getNode();
      $xp = $textnode->getNodePath();

      $doSkip = false;
      foreach (self::$skipTagsForRemoveWhitespace as $pattern) {
        if (strpos($xp, "/$pattern") !== false) {
          $doSkip = true;
          break;
        }
      }

      if ($doSkip) {
        continue;
      }

      $textnode->nodeValue = preg_replace("/\s{2,}/", ' ', $textnode->nodeValue);
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }

  /**
   * Callback function for preg_replace_callback use.
   *
   * @param  array $matches PREG matches
   *
   * @return string
   */
  private function restoreProtectedHtml($matches)
  {
    preg_match('/.*"(?<id>\d*)"/', $matches['attributes'], $matchesInner);

    $htmlChild = '';
    if (isset($this->protectedChildNodes[$matchesInner['id']])) {
      foreach ($this->protectedChildNodes[$matchesInner['id']] as $childNode) {
        $htmlChild .= $childNode;
      }
    }

    return $htmlChild;
  }

  /**
   * Sort HTML-Attributes, so that gzip can do better work
   *  and remove some default attributes.
   *
   * @param SimpleHtmlDom $element
   * @param array         $attributs
   *
   * @return bool
   */
  private function optimizeAttributes(SimpleHtmlDom $element, &$attributs)
  {
    if (!$attributs) {
      return false;
    }

    $attrs = array();
    foreach ($attributs as $attrName => $attrValue) {

      if (in_array($attrName, self::$booleanAttributes, true)) {
        $attrs[$attrName] = $this->booleanAttributesHelper;
        $element->{$attrName} = null;
        continue;
      }

      if (
          ($attrName === 'href' || $attrName === 'src' || $attrName === 'action')
          &&
          !(isset($attributs['rel']) && $attributs['rel'] === 'external')
          &&
          !(isset($attributs['target']) && $attributs['target'] === '_blank')
      ) {
        $attrValue = str_replace('http://', '//', $attrValue);
      }

      if ($this->optimizeAttributesFilters($element->tag, $attrName, $attrValue, $attributs)) {
        $element->{$attrName} = null;
        continue;
      }

      $attrValue = $this->sortCssClasses($attrName, $attrValue);

      $attrs[$attrName] = $attrValue;
      $element->{$attrName} = null;
    }

    ksort($attrs);
    foreach ($attrs as $attrName => $attrValue) {
      $element->setAttribute($attrName, $attrValue, true);
    }

    return true;
  }

  /**
   * Check if the attribute (key / value) is default and can be skipped.
   *
   * @param string $tag
   * @param string $attrName
   * @param string $attrValue
   * @param string $allAttr
   *
   * @return bool
   */
  private function optimizeAttributesFilters($tag, $attrName, $attrValue, $allAttr)
  {
    // remove default
    if ($tag === 'script' && $attrName === 'language' && $attrValue === 'javascript') {
      return true;
    }

    // remove default
    if ($tag === 'form' && $attrName === 'method' && $attrValue === 'get') {
      return true;
    }

    // remove default
    if ($tag === 'input' && $attrName === 'type' && $attrValue === 'text') {
      return true;
    }

    // remove default
    if ($tag === 'area' && $attrName === 'shape' && $attrValue === 'rect') {
      return true;
    }

    // remove deprecated charset-attribute (the Browser will use the charset from the HTTP-Header, anyway)
    if ($tag === 'script' && $attrName === 'charset' && !isset($allAttr['src'])) {
      return true;
    }

    // remove deprecated anchor-jump
    if ($tag === 'a' && $attrName === 'name' && isset($allAttr['id'])) {
      return true;
    }

    // remove "type=text/css" for css links
    if ($tag === 'link' && $attrName === 'type' && $attrValue === 'text/css' && isset($allAttr['rel']) && $allAttr['rel'] === 'stylesheet') {
      return true;
    }

    // remove deprecated script-mime-types
    if ($tag === 'script' && $attrName === 'type' && isset($allAttr['src']) && in_array($attrValue, self::$executableScriptsMimeTypes, true)) {
      return true;
    }

    // remove empty value from <input>
    if ($tag === 'input' && $attrName === 'value' && $attrValue === '') {
      return true;
    }

    // remove some empty attribute
    if ($attrValue === '' && preg_match('/^(?:class|id|style|title|lang|dir|on(?:focus|blur|change|click|dblclick|mouse(?:down|up|over|move|out)|key(?:press|down|up)))$/', $attrName)) {
      return true;
    }

    return false;
  }

  /**
   * @param $attrName
   * @param $attrValue
   *
   * @return string
   */
  private function sortCssClasses($attrName, $attrValue)
  {
    if ($attrName !== 'class' || !$attrValue) {
      return $attrValue;
    }

    $classes = explode(' ', $attrValue);
    if (!$classes) {
      return '';
    }

    sort($classes);
    $attrValue = '';
    foreach ($classes as $class) {
      if (!$class) {
        continue;
      }
      $attrValue .= trim($class) . ' ';
    }
    $attrValue = trim($attrValue);

    return $attrValue;
  }
}
