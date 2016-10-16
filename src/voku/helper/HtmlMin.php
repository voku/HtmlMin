<?php

namespace voku\helper;

/**
 * Class HtmlMin
 *
 * Inspired by:
 * - JS: https://github.com/kangax/html-minifier/blob/gh-pages/src/htmlminifier.js
 * - PHP: https://github.com/searchturbine/phpwee-php-minifier
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
  protected $randomHash;

  /**
   * HtmlMin constructor.
   */
  public function __construct()
  {
    $this->randomHash = md5(Bootup::get_random_bytes(16));
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

    $origHtml = $html;
    $origHtmlLength = UTF8::strlen($html);

    $dom = new HtmlDomParser();
    $dom->getDocument()->preserveWhiteSpace = false;
    $dom->getDocument()->formatOutput = false;

    $dom->loadHtml($html);
    $xpath = new \DOMXPath($dom->getDocument());

    foreach ($xpath->query('//comment()') as $comment) {
      $val = $comment->nodeValue;
      if (strpos($val, '[') !== 0) {
        $comment->parentNode->removeChild($comment);
      }
    }

    $dom->getDocument()->normalizeDocument();

    $textnodes = $xpath->query('//text()');
    $skip = array('style', 'pre', 'code', 'script', 'textarea');
    foreach ($textnodes as $t) {
      /* @var $t \DOMNode */
      $xp = $t->getNodePath();

      $doSkip = false;
      foreach ($skip as $pattern) {
        if (strpos($xp, "/$pattern") !== false) {
          $doSkip = true;
          break;
        }
      }

      if ($doSkip) {
        continue;
      }

      $t->nodeValue = preg_replace("/\s{2,}/", ' ', $t->nodeValue);
    }

    $dom->getDocument()->normalizeDocument();

    $divnodes = $xpath->query('//div|//p|//nav|//footer|//article|//script|//hr|//br');
    foreach ($divnodes as $d) {
      $candidates = array();

      if (count($d->childNodes)) {
        $candidates[] = $d->firstChild;
        $candidates[] = $d->lastChild;
        $candidates[] = $d->previousSibling;
        $candidates[] = $d->nextSibling;
      }

      foreach ($candidates as $c) {
        if ($c === null) {
          continue;
        }

        if ($c->nodeType === 3) {
          $c->nodeValue = trim($c->nodeValue);
        }
      }
    }

    $dom->getDocument()->normalizeDocument();

    $elements = $dom->find('*');
    foreach ($elements as $element) {
      if (count($element) > 1) {
        foreach ($element as $e) {
          $this->optimizeAttributes($e);
        }
      } else {
        $this->optimizeAttributes($element);
      }
    }

    $dom->getDocument()->normalizeDocument();

    // ------------------------------------

    $html = UTF8::cleanup($dom->html());
    // final clean-up
    $html = str_replace(
        array(
            'html>' . "\n",
            "\n" . '<html',
            '<!doctype',
            '="delete-this-' . $this->randomHash . '"',
        ),
        array(
            'html>',
            '<html',
            '<!DOCTYPE',
            '',
        ),
        $html
    );

    if ($origHtmlLength < UTF8::strlen($html)) {
      $html = $origHtml;
    }

    // Remove spaces that are followed by either > or <
    $html = preg_replace('/ (>)/', '$1', $html);
    // Remove spaces that are preceded by either > or <
    $html = preg_replace('/(<) /', '$1', $html);
    // Remove spaces that are between > and <
    $html = preg_replace('/(>) (<)/', '>$2', $html);

    return $html;
  }

  /**
   * Sort HTML-Attributes, so that gzip can do better work
   *  and remove some default attributes.
   *
   * @param SimpleHtmlDom $element
   *
   * @return bool
   */
  private function optimizeAttributes(SimpleHtmlDom $element)
  {
    $attributs = $element->getAllAttributes();

    if (!$attributs) {
      return false;
    }

    /*
    if (
        ($element->tag === 'script' || $element->tag === 'style')
        &&
        !isset($attributs['src'])
    ) {
      // TODO: protect inline css / js
    }
    */

    $attrs = array();
    foreach ((array)$attributs as $attrName => $attrValue) {

      if (in_array($attrName, self::$booleanAttributes, true)) {
        $attrs[$attrName] = 'delete-this-' . $this->randomHash;
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
