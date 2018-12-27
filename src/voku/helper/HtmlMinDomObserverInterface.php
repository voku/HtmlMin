<?php

declare(strict_types=1);

namespace voku\helper;

interface HtmlMinDomObserverInterface
{
    /**
     * Receive dom elements before the minification.
     *
     * @param SimpleHtmlDom $element
     * @param HtmlMin       $htmlMin
     */
    public function domElementBeforeMinification(SimpleHtmlDom $element, HtmlMin $htmlMin);

    /**
     * Receive dom elements after the minification.
     *
     * @param SimpleHtmlDom $element
     * @param HtmlMin       $htmlMin
     */
    public function domElementAfterMinification(SimpleHtmlDom $element, HtmlMin $htmlMin);
}
