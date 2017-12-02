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
   * @var string
   */
  private static $regExSpace = "/[[:space:]]{2,}|[\r\n]+/u";

  /**
   * // https://mathiasbynens.be/demo/javascript-mime-type
   * // https://developer.mozilla.org/en/docs/Web/HTML/Element/script#attr-type
   *
   * @var array
   */
  private static $executableScriptsMimeTypes = array(
      'text/javascript'          => '',
      'text/ecmascript'          => '',
      'text/jscript'             => '',
      'application/javascript'   => '',
      'application/x-javascript' => '',
      'application/ecmascript'   => '',
  );

  private static $selfClosingTags = array(
      'area',
      'base',
      'basefont',
      'br',
      'col',
      'command',
      'embed',
      'frame',
      'hr',
      'img',
      'input',
      'isindex',
      'keygen',
      'link',
      'meta',
      'param',
      'source',
      'track',
      'wbr',
  );

  private static $trimWhitespaceFromTags = array(
      'article' => '',
      'br'      => '',
      'div'     => '',
      'footer'  => '',
      'hr'      => '',
      'nav'     => '',
      'p'       => '',
      'script'  => '',
  );

  /**
   * @var array
   */
  private static $booleanAttributes = array(
      'allowfullscreen' => '',
      'async'           => '',
      'autofocus'       => '',
      'autoplay'        => '',
      'checked'         => '',
      'compact'         => '',
      'controls'        => '',
      'declare'         => '',
      'default'         => '',
      'defaultchecked'  => '',
      'defaultmuted'    => '',
      'defaultselected' => '',
      'defer'           => '',
      'disabled'        => '',
      'enabled'         => '',
      'formnovalidate'  => '',
      'hidden'          => '',
      'indeterminate'   => '',
      'inert'           => '',
      'ismap'           => '',
      'itemscope'       => '',
      'loop'            => '',
      'multiple'        => '',
      'muted'           => '',
      'nohref'          => '',
      'noresize'        => '',
      'noshade'         => '',
      'novalidate'      => '',
      'nowrap'          => '',
      'open'            => '',
      'pauseonexit'     => '',
      'readonly'        => '',
      'required'        => '',
      'reversed'        => '',
      'scoped'          => '',
      'seamless'        => '',
      'selected'        => '',
      'sortable'        => '',
      'truespeed'       => '',
      'typemustmatch'   => '',
      'visible'         => '',
  );
  /**
   * @var array
   */
  private static $skipTagsForRemoveWhitespace = array(
      'code',
      'pre',
      'script',
      'style',
      'textarea',
  );

  /**
   * @var array
   */
  private $protectedChildNodes = array();

  /**
   * @var string
   */
  private $protectedChildNodesHelper = 'html-min--voku--saved-content';

  /**
   * @var string
   */
  private $booleanAttributesHelper = 'html-min--voku--delete-this';

  /**
   * @var bool
   */
  private $doOptimizeViaHtmlDomParser = true;

  /**
   * @var bool
   */
  private $doOptimizeAttributes = true;

  /**
   * @var bool
   */
  private $doRemoveComments = true;

  /**
   * @var bool
   */
  private $doRemoveWhitespaceAroundTags = true;

  /**
   * @var bool
   */
  private $doRemoveHttpPrefixFromAttributes = false;


  /**
   * @var array
   */
  private $domainsToRemoveHttpPrefixFromAttributes = array(
      'google.com',
      'google.de',
  );

  /**
   * @var bool
   */
  private $doSortCssClassNames = true;

  /**
   * @var bool
   */
  private $doSortHtmlAttributes = true;

  /**
   * @var bool
   */
  private $doRemoveDeprecatedScriptCharsetAttribute = true;

  /**
   * @var bool
   */
  private $doRemoveDefaultAttributes = false;

  /**
   * @var bool
   */
  private $doRemoveDeprecatedAnchorName = true;

  /**
   * @var bool
   */
  private $doRemoveDeprecatedTypeFromStylesheetLink = true;

  /**
   * @var bool
   */
  private $doRemoveDeprecatedTypeFromScriptTag = true;

  /**
   * @var bool
   */
  private $doRemoveValueFromEmptyInput = true;

  /**
   * @var bool
   */
  private $doRemoveEmptyAttributes = true;

  /**
   * @var bool
   */
  private $doSumUpWhitespace = true;

  /**
   * @var bool
   */
  private $doRemoveSpacesBetweenTags = false;

  /**
   * HtmlMin constructor.
   */
  public function __construct()
  {
  }

  /**
   * @param boolean $doOptimizeAttributes
   *
   * @return $this
   */
  public function doOptimizeAttributes($doOptimizeAttributes = true)
  {
    $this->doOptimizeAttributes = $doOptimizeAttributes;

    return $this;
  }

  /**
   * @param boolean $doOptimizeViaHtmlDomParser
   *
   * @return $this
   */
  public function doOptimizeViaHtmlDomParser($doOptimizeViaHtmlDomParser = true)
  {
    $this->doOptimizeViaHtmlDomParser = $doOptimizeViaHtmlDomParser;

    return $this;
  }

  /**
   * @param boolean $doRemoveComments
   *
   * @return $this
   */
  public function doRemoveComments($doRemoveComments = true)
  {
    $this->doRemoveComments = $doRemoveComments;

    return $this;
  }

  /**
   * @param boolean $doRemoveDefaultAttributes
   *
   * @return $this
   */
  public function doRemoveDefaultAttributes($doRemoveDefaultAttributes = true)
  {
    $this->doRemoveDefaultAttributes = $doRemoveDefaultAttributes;

    return $this;
  }

  /**
   * @param boolean $doRemoveDeprecatedAnchorName
   *
   * @return $this
   */
  public function doRemoveDeprecatedAnchorName($doRemoveDeprecatedAnchorName = true)
  {
    $this->doRemoveDeprecatedAnchorName = $doRemoveDeprecatedAnchorName;

    return $this;
  }

  /**
   * @param boolean $doRemoveDeprecatedScriptCharsetAttribute
   *
   * @return $this
   */
  public function doRemoveDeprecatedScriptCharsetAttribute($doRemoveDeprecatedScriptCharsetAttribute = true)
  {
    $this->doRemoveDeprecatedScriptCharsetAttribute = $doRemoveDeprecatedScriptCharsetAttribute;

    return $this;
  }

  /**
   * @param boolean $doRemoveDeprecatedTypeFromScriptTag
   *
   * @return $this
   */
  public function doRemoveDeprecatedTypeFromScriptTag($doRemoveDeprecatedTypeFromScriptTag = true)
  {
    $this->doRemoveDeprecatedTypeFromScriptTag = $doRemoveDeprecatedTypeFromScriptTag;

    return $this;
  }

  /**
   * @param boolean $doRemoveDeprecatedTypeFromStylesheetLink
   *
   * @return $this
   */
  public function doRemoveDeprecatedTypeFromStylesheetLink($doRemoveDeprecatedTypeFromStylesheetLink = true)
  {
    $this->doRemoveDeprecatedTypeFromStylesheetLink = $doRemoveDeprecatedTypeFromStylesheetLink;

    return $this;
  }

  /**
   * @param boolean $doRemoveEmptyAttributes
   *
   * @return $this
   */
  public function doRemoveEmptyAttributes($doRemoveEmptyAttributes = true)
  {
    $this->doRemoveEmptyAttributes = $doRemoveEmptyAttributes;

    return $this;
  }

  /**
   * @param boolean $doRemoveHttpPrefixFromAttributes
   *
   * @return $this
   */
  public function doRemoveHttpPrefixFromAttributes($doRemoveHttpPrefixFromAttributes = true)
  {
    $this->doRemoveHttpPrefixFromAttributes = $doRemoveHttpPrefixFromAttributes;

    return $this;
  }

  /**
   * @param boolean $doRemoveValueFromEmptyInput
   *
   * @return $this
   */
  public function doRemoveValueFromEmptyInput($doRemoveValueFromEmptyInput = true)
  {
    $this->doRemoveValueFromEmptyInput = $doRemoveValueFromEmptyInput;

    return $this;
  }

  /**
   * @param boolean $doRemoveWhitespaceAroundTags
   *
   * @return $this
   */
  public function doRemoveWhitespaceAroundTags($doRemoveWhitespaceAroundTags = true)
  {
    $this->doRemoveWhitespaceAroundTags = $doRemoveWhitespaceAroundTags;

    return $this;
  }

  /**
   * @param boolean $doSortCssClassNames
   *
   * @return $this
   */
  public function doSortCssClassNames($doSortCssClassNames = true)
  {
    $this->doSortCssClassNames = $doSortCssClassNames;

    return $this;
  }

  /**
   * @param boolean $doSortHtmlAttributes
   *
   * @return $this
   */
  public function doSortHtmlAttributes($doSortHtmlAttributes = true)
  {
    $this->doSortHtmlAttributes = $doSortHtmlAttributes;

    return $this;
  }

  /**
   * @param boolean $doSumUpWhitespace
   *
   * @return $this
   */
  public function doSumUpWhitespace($doSumUpWhitespace = true)
  {
    $this->doSumUpWhitespace = $doSumUpWhitespace;

    return $this;
  }

  /**
   * @param boolean $doRemoveSpacesBetweenTags
   *
   * @return $this
   */
  public function doRemoveSpacesBetweenTags($doRemoveSpacesBetweenTags = true)
  {
    $this->doRemoveSpacesBetweenTags = $doRemoveSpacesBetweenTags;

    return $this;
  }

  /**
   * Check if the current string is an conditional comment.
   *
   * INFO: since IE >= 10 conditional comment are not working anymore
   *
   * <!--[if expression]> HTML <![endif]-->
   * <![if expression]> HTML <![endif]>
   *
   * @param string $comment
   *
   * @return bool
   */
  private function isConditionalComment($comment)
  {
    if (preg_match('/^\[if [^\]]+\]/', $comment)) {
      return true;
    }

    if (preg_match('/\[endif\]$/', $comment)) {
      return true;
    }

    return false;
  }

  /**
   * @param string $html
   * @param bool   $decodeUtf8Specials <p>Use this only in special cases, e.g. for PHP 5.3</p>
   *
   * @return string
   */
  public function minify($html, $decodeUtf8Specials = false)
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
    static $CACHE_SELF_CLOSING_TAGS = null;
    if ($CACHE_SELF_CLOSING_TAGS === null) {
      $CACHE_SELF_CLOSING_TAGS = implode('|', self::$selfClosingTags);
    }

    // reset
    $this->protectedChildNodes = array();

    // save old content
    $origHtml = $html;
    $origHtmlLength = UTF8::strlen($html);

    // -------------------------------------------------------------------------
    // Minify the HTML via "HtmlDomParser"
    // -------------------------------------------------------------------------

    if ($this->doOptimizeViaHtmlDomParser === true) {
      $html = $this->minifyHtmlDom($html, $decodeUtf8Specials);
    }

    // -------------------------------------------------------------------------
    // Trim whitespace from html-string. [protected html is still protected]
    // -------------------------------------------------------------------------

    // Remove extra white-space(s) between HTML attribute(s)
    $html = (string)\preg_replace_callback(
        '#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#',
        function ($matches) {
          return '<' . $matches[1] . (string)\preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        },
        $html
    );


    if($this->doRemoveSpacesBetweenTags === true){
      // Remove spaces that are between > and <
      $html = (string)\preg_replace('/(>) (<)/', '>$2', $html);
    }
    // -------------------------------------------------------------------------
    // Restore protected HTML-code.
    // -------------------------------------------------------------------------

    $html = (string)\preg_replace_callback(
        '/<(?<element>' . $this->protectedChildNodesHelper . ')(?<attributes> [^>]*)?>(?<value>.*?)<\/' . $this->protectedChildNodesHelper . '>/',
        array($this, 'restoreProtectedHtml'),
        $html
    );

    // -------------------------------------------------------------------------
    // Restore protected HTML-entities.
    // -------------------------------------------------------------------------

    if ($this->doOptimizeViaHtmlDomParser === true) {
      $html = HtmlDomParser::putReplacedBackToPreserveHtmlEntities($html);
    }

    // ------------------------------------
    // Final clean-up
    // ------------------------------------

    $html = UTF8::cleanup($html);

    $html = \str_replace(
        array(
            'html>' . "\n",
            "\n" . '<html',
            'html/>' . "\n",
            "\n" . '</html',
            'head>' . "\n",
            "\n" . '<head',
            'head/>' . "\n",
            "\n" . '</head',
            '="' . $this->booleanAttributesHelper . '"',
        ),
        array(
            'html>',
            '<html',
            'html/>',
            '</html',
            'head>',
            '<head',
            'head/>',
            '</head',
            '',
        ),
        $html
    );

    $html = (string)\preg_replace('#<\b(' . $CACHE_SELF_CLOSING_TAGS . ')([^>]+)><\/\b\1>#', '<\\1\\2/>', $html);

    // ------------------------------------
    // check if compression worked
    // ------------------------------------

    if ($origHtmlLength < UTF8::strlen($html)) {
      $html = $origHtml;
    }

    return $html;
  }

  /**
   * @param $html
   * @param $decodeUtf8Specials
   *
   * @return string
   */
  private function minifyHtmlDom($html, $decodeUtf8Specials)
  {
    // init dom
    $dom = new HtmlDomParser();
    $dom->getDocument()->preserveWhiteSpace = false; // remove redundant white space
    $dom->getDocument()->formatOutput = false; // do not formats output with indentation

    // load dom
    $dom->loadHtml($html);

    // -------------------------------------------------------------------------
    // Protect HTML tags and conditional comments.
    // -------------------------------------------------------------------------

    $dom = $this->protectTags($dom);

    // -------------------------------------------------------------------------
    // Remove default HTML comments. [protected html is still protected]
    // -------------------------------------------------------------------------

    if ($this->doRemoveComments === true) {
      $dom = $this->removeComments($dom);
    }

    // -------------------------------------------------------------------------
    // Sum-Up extra whitespace from the Dom. [protected html is still protected]
    // -------------------------------------------------------------------------

    if ($this->doSumUpWhitespace === true) {
      $dom = $this->sumUpWhitespace($dom);
    }

    foreach ($dom->find('*') as $element) {

      // -------------------------------------------------------------------------
      // Optimize html attributes. [protected html is still protected]
      // -------------------------------------------------------------------------

      if ($this->doOptimizeAttributes === true) {
        $this->optimizeAttributes($element);
      }

      // -------------------------------------------------------------------------
      // Remove whitespace around tags. [protected html is still protected]
      // -------------------------------------------------------------------------

      if ($this->doRemoveWhitespaceAroundTags === true) {
        $this->removeWhitespaceAroundTags($element);
      }
    }

    // -------------------------------------------------------------------------
    // Convert the Dom into a string.
    // -------------------------------------------------------------------------

    $html = $dom->html($decodeUtf8Specials);

    return $html;
  }

  /**
   * Sort HTML-Attributes, so that gzip can do better work and remove some default attributes...
   *
   * @param SimpleHtmlDom $element
   *
   * @return bool
   */
  private function optimizeAttributes(SimpleHtmlDom $element)
  {
    $attributes = $element->getAllAttributes();
    if ($attributes === null) {
      return false;
    }

    $attrs = array();
    foreach ((array)$attributes as $attrName => $attrValue) {

      if (isset(self::$booleanAttributes[$attrName])) {

        if ($this->doSortHtmlAttributes === true) {
          $attrs[$attrName] = $this->booleanAttributesHelper;
          $element->{$attrName} = null;
        }

        continue;
      }

      // -------------------------------------------------------------------------
      // Remove optional "http:"-prefix from attributes.
      // -------------------------------------------------------------------------

      if ($this->doRemoveHttpPrefixFromAttributes === true) {
        if (
            ($attrName === 'href' || $attrName === 'src' || $attrName === 'action')
            &&
            !(isset($attributes['rel']) && $attributes['rel'] === 'external')
            &&
            !(isset($attributes['target']) && $attributes['target'] === '_blank')
        ) {
          $attrValue = \str_replace('http://', '//', $attrValue);
        }
      }

      if ($this->removeAttributeHelper($element->tag, $attrName, $attrValue, $attributes)) {
        $element->{$attrName} = null;
        continue;
      }

      // -------------------------------------------------------------------------
      // Sort css-class-names, for better gzip results.
      // -------------------------------------------------------------------------

      if ($this->doSortCssClassNames === true) {
        $attrValue = $this->sortCssClassNames($attrName, $attrValue);
      }

      if ($this->doSortHtmlAttributes === true) {
        $attrs[$attrName] = $attrValue;
        $element->{$attrName} = null;
      }
    }

    // -------------------------------------------------------------------------
    // Sort html-attributes, for better gzip results.
    // -------------------------------------------------------------------------

    if ($this->doSortHtmlAttributes === true) {
      \ksort($attrs);
      foreach ($attrs as $attrName => $attrValue) {
        $attrValue = HtmlDomParser::replaceToPreserveHtmlEntities($attrValue);
        $element->setAttribute($attrName, $attrValue, true);
      }
    }

    return true;
  }

  /**
   * Prevent changes of inline "styles" and "scripts".
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function protectTags(HtmlDomParser $dom)
  {
    // init
    $counter = 0;

    foreach ($dom->find('script, style') as $element) {

      // skip external links
      if ($element->tag === 'script' || $element->tag === 'style') {
        $attributes = $element->getAllAttributes();
        if (isset($attributes['src'])) {
          continue;
        }
      }

      $this->protectedChildNodes[$counter] = $element->text();
      $element->getNode()->nodeValue = '<' . $this->protectedChildNodesHelper . ' data-' . $this->protectedChildNodesHelper . '="' . $counter . '"></' . $this->protectedChildNodesHelper . '>';

      ++$counter;
    }

    $dom->getDocument()->normalizeDocument();

    foreach ($dom->find('//comment()') as $element) {
      $text = $element->text();

      // skip normal comments
      if ($this->isConditionalComment($text) === false) {
        continue;
      }

      $this->protectedChildNodes[$counter] = '<!--' . $text . '-->';

      /* @var $node \DOMComment */
      $node = $element->getNode();
      $child = new \DOMText('<' . $this->protectedChildNodesHelper . ' data-' . $this->protectedChildNodesHelper . '="' . $counter . '"></' . $this->protectedChildNodesHelper . '>');
      $element->getNode()->parentNode->replaceChild($child, $node);

      ++$counter;
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }

  /**
   * Check if the attribute can be removed.
   *
   * @param string $tag
   * @param string $attrName
   * @param string $attrValue
   * @param array  $allAttr
   *
   * @return bool
   */
  private function removeAttributeHelper($tag, $attrName, $attrValue, $allAttr)
  {
    // remove defaults
    if ($this->doRemoveDefaultAttributes === true) {

      if ($tag === 'script' && $attrName === 'language' && $attrValue === 'javascript') {
        return true;
      }

      if ($tag === 'form' && $attrName === 'method' && $attrValue === 'get') {
        return true;
      }

      if ($tag === 'input' && $attrName === 'type' && $attrValue === 'text') {
        return true;
      }

      if ($tag === 'area' && $attrName === 'shape' && $attrValue === 'rect') {
        return true;
      }
    }

    // remove deprecated charset-attribute (the browser will use the charset from the HTTP-Header, anyway)
    if ($this->doRemoveDeprecatedScriptCharsetAttribute === true) {
      if ($tag === 'script' && $attrName === 'charset' && !isset($allAttr['src'])) {
        return true;
      }
    }

    // remove deprecated anchor-jump
    if ($this->doRemoveDeprecatedAnchorName === true) {
      if ($tag === 'a' && $attrName === 'name' && isset($allAttr['id']) && $allAttr['id'] === $attrValue) {
        return true;
      }
    }

    // remove "type=text/css" for css links
    if ($this->doRemoveDeprecatedTypeFromStylesheetLink === true) {
      if ($tag === 'link' && $attrName === 'type' && $attrValue === 'text/css' && isset($allAttr['rel']) && $allAttr['rel'] === 'stylesheet') {
        return true;
      }
    }

    // remove deprecated script-mime-types
    if ($this->doRemoveDeprecatedTypeFromScriptTag === true) {
      if ($tag === 'script' && $attrName === 'type' && isset($allAttr['src'], self::$executableScriptsMimeTypes[$attrValue])) {
        return true;
      }
    }

    // remove 'value=""' from <input type="text">
    if ($this->doRemoveValueFromEmptyInput === true) {
      if ($tag === 'input' && $attrName === 'value' && $attrValue === '' && isset($allAttr['type']) && $allAttr['type'] === 'text') {
        return true;
      }
    }

    // remove some empty attributes
    if ($this->doRemoveEmptyAttributes === true) {
      if (\trim($attrValue) === '' && \preg_match('/^(?:class|id|style|title|lang|dir|on(?:focus|blur|change|click|dblclick|mouse(?:down|up|over|move|out)|key(?:press|down|up)))$/', $attrName)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Remove comments in the dom.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function removeComments(HtmlDomParser $dom)
  {
    foreach ($dom->find('//comment()') as $commentWrapper) {
      $comment = $commentWrapper->getNode();
      $val = $comment->nodeValue;
      if (\strpos($val, '[') === false) {
        $comment->parentNode->removeChild($comment);
      }
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }

  /**
   * Trim tags in the dom.
   *
   * @param SimpleHtmlDom $element
   *
   * @return void
   */
  private function removeWhitespaceAroundTags(SimpleHtmlDom $element)
  {
    if (isset(self::$trimWhitespaceFromTags[$element->tag])) {
      $node = $element->getNode();

      $candidates = array();
      if ($node->childNodes->length > 0) {
        $candidates[] = $node->firstChild;
        $candidates[] = $node->lastChild;
        $candidates[] = $node->previousSibling;
        $candidates[] = $node->nextSibling;
      }

      foreach ($candidates as &$candidate) {
        if ($candidate === null) {
          continue;
        }

        if ($candidate->nodeType === 3) {
          $candidate->nodeValue = \preg_replace(self::$regExSpace, ' ', $candidate->nodeValue);
        }
      }
    }
  }

  /**
   * Callback function for preg_replace_callback use.
   *
   * @param array $matches PREG matches
   *
   * @return string
   */
  private function restoreProtectedHtml($matches)
  {
    \preg_match('/.*"(?<id>\d*)"/', $matches['attributes'], $matchesInner);

    $html = '';
    if (isset($this->protectedChildNodes[$matchesInner['id']])) {
      $html .= $this->protectedChildNodes[$matchesInner['id']];
    }

    return $html;
  }

  /**
   * @param array $domainsToRemoveHttpPrefixFromAttributes
   *
   * @return $this
   */
  public function setDomainsToRemoveHttpPrefixFromAttributes($domainsToRemoveHttpPrefixFromAttributes)
  {
    $this->domainsToRemoveHttpPrefixFromAttributes = $domainsToRemoveHttpPrefixFromAttributes;

    return $this;
  }

  /**
   * @param $attrName
   * @param $attrValue
   *
   * @return string
   */
  private function sortCssClassNames($attrName, $attrValue)
  {
    if ($attrName !== 'class' || !$attrValue) {
      return $attrValue;
    }

    $classes = \array_unique(
        \explode(' ', $attrValue)
    );
    \sort($classes);

    $attrValue = '';
    foreach ($classes as $class) {

      if (!$class) {
        continue;
      }

      $attrValue .= \trim($class) . ' ';
    }
    $attrValue = \trim($attrValue);

    return $attrValue;
  }

  /**
   * Sum-up extra whitespace from dom-nodes.
   *
   * @param HtmlDomParser $dom
   *
   * @return HtmlDomParser
   */
  private function sumUpWhitespace(HtmlDomParser $dom)
  {
    $textnodes = $dom->find('//text()');
    foreach ($textnodes as $textnodeWrapper) {
      /* @var $textnode \DOMNode */
      $textnode = $textnodeWrapper->getNode();
      $xp = $textnode->getNodePath();

      $doSkip = false;
      foreach (self::$skipTagsForRemoveWhitespace as $pattern) {
        if (\strpos($xp, "/$pattern") !== false) {
          $doSkip = true;
          break;
        }
      }
      if ($doSkip) {
        continue;
      }

      $textnode->nodeValue = \preg_replace(self::$regExSpace, ' ', $textnode->nodeValue);
    }

    $dom->getDocument()->normalizeDocument();

    return $dom;
  }
}
