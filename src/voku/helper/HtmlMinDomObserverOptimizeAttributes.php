<?php

declare(strict_types=1);

namespace voku\helper;

/**
 * HtmlMinDomObserverOptimizeAttributes: Optimize html attributes. [protected html is still protected]
 *
 * Sort HTML-Attributes, so that gzip can do better work and remove some default attributes...
 */
final class HtmlMinDomObserverOptimizeAttributes implements HtmlMinDomObserverInterface
{
    /**
     * // https://mathiasbynens.be/demo/javascript-mime-type
     * // https://developer.mozilla.org/en/docs/Web/HTML/Element/script#attr-type
     *
     * @var array
     */
    private static $executableScriptsMimeTypes = [
        'text/javascript'          => '',
        'text/ecmascript'          => '',
        'text/jscript'             => '',
        'application/javascript'   => '',
        'application/x-javascript' => '',
        'application/ecmascript'   => '',
    ];

    /**
     * Receive dom elements before the minification.
     *
     * @param SimpleHtmlDom $element
     * @param HtmlMin       $htmlMin
     */
    public function domElementBeforeMinification(SimpleHtmlDom $element, HtmlMin $htmlMin) {

    }

    /**
     * Receive dom elements after the minification.
     *
     * @param SimpleHtmlDom $element
     * @param HtmlMin       $htmlMin
     */
    public function domElementAfterMinification(SimpleHtmlDom $element, HtmlMin $htmlMin) {
        $attributes = $element->getAllAttributes();
        if ($attributes === null) {
            return;
        }

        $attrs = [];
        foreach ((array) $attributes as $attrName => $attrValue) {

            // -------------------------------------------------------------------------
            // Remove optional "http:"-prefix from attributes.
            // -------------------------------------------------------------------------

            if ($htmlMin->isDoRemoveHttpPrefixFromAttributes() === true) {
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

            if ($this->removeAttributeHelper($element->tag, $attrName, $attrValue, $attributes, $htmlMin)) {
                $element->{$attrName} = null;

                continue;
            }

            // -------------------------------------------------------------------------
            // Sort css-class-names, for better gzip results.
            // -------------------------------------------------------------------------

            if ($htmlMin->isDoSortCssClassNames() === true) {
                $attrValue = $this->sortCssClassNames($attrName, $attrValue);
            }

            if ($htmlMin->isDoSortHtmlAttributes() === true) {
                $attrs[$attrName] = $attrValue;
                $element->{$attrName} = null;
            }
        }

        // -------------------------------------------------------------------------
        // Sort html-attributes, for better gzip results.
        // -------------------------------------------------------------------------

        if ($htmlMin->isDoSortHtmlAttributes() === true) {
            \ksort($attrs);
            foreach ($attrs as $attrName => $attrValue) {
                $attrValue = HtmlDomParser::replaceToPreserveHtmlEntities($attrValue);
                $element->setAttribute($attrName, $attrValue, true);
            }
        }
    }


    /**
     * Check if the attribute can be removed.
     *
     * @param string  $tag
     * @param string  $attrName
     * @param string  $attrValue
     * @param array   $allAttr
     * @param HtmlMin $htmlMin
     *
     * @return bool
     */
    private function removeAttributeHelper($tag, $attrName, $attrValue, $allAttr, HtmlMin $htmlMin): bool
    {
        // remove defaults
        if ($htmlMin->isDoRemoveDefaultAttributes() === true) {
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
        if ($htmlMin->isDoRemoveDeprecatedScriptCharsetAttribute() === true) {
            if ($tag === 'script' && $attrName === 'charset' && !isset($allAttr['src'])) {
                return true;
            }
        }

        // remove deprecated anchor-jump
        if ($htmlMin->isDoRemoveDeprecatedAnchorName() === true) {
            if ($tag === 'a' && $attrName === 'name' && isset($allAttr['id']) && $allAttr['id'] === $attrValue) {
                return true;
            }
        }

        // remove "type=text/css" for css links
        if ($htmlMin->isDoRemoveDeprecatedTypeFromStylesheetLink() === true) {
            if ($tag === 'link' && $attrName === 'type' && $attrValue === 'text/css' && isset($allAttr['rel']) && $allAttr['rel'] === 'stylesheet') {
                return true;
            }
        }

        // remove deprecated script-mime-types
        if ($htmlMin->isDoRemoveDeprecatedTypeFromScriptTag() === true) {
            if ($tag === 'script' && $attrName === 'type' && isset($allAttr['src'], self::$executableScriptsMimeTypes[$attrValue])) {
                return true;
            }
        }

        // remove 'value=""' from <input type="text">
        if ($htmlMin->isDoRemoveValueFromEmptyInput() === true) {
            if ($tag === 'input' && $attrName === 'value' && $attrValue === '' && isset($allAttr['type']) && $allAttr['type'] === 'text') {
                return true;
            }
        }

        // remove some empty attributes
        if ($htmlMin->isDoRemoveEmptyAttributes() === true) {
            if (\trim($attrValue) === '' && \preg_match('/^(?:class|id|style|title|lang|dir|on(?:focus|blur|change|click|dblclick|mouse(?:down|up|over|move|out)|key(?:press|down|up)))$/', $attrName)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $attrName
     * @param $attrValue
     *
     * @return string
     */
    private function sortCssClassNames($attrName, $attrValue): string
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

        return \trim($attrValue);
    }
}
