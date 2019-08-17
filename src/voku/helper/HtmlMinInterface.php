<?php

namespace voku\helper;

interface HtmlMinInterface
{
    /**
     * @param string $html
     * @param bool   $decodeUtf8Specials
     *
     * @return string
     */
    public function minify($html, $decodeUtf8Specials = false): string;

    /**
     * @return bool
     */
    public function isDoSortCssClassNames(): bool;

    /**
     * @return bool
     */
    public function isDoSortHtmlAttributes(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveDeprecatedScriptCharsetAttribute(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveDefaultAttributes(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveDeprecatedAnchorName(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveDeprecatedTypeFromStylesheetLink(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveDeprecatedTypeFromScriptTag(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveValueFromEmptyInput(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveEmptyAttributes(): bool;

    /**
     * @return bool
     */
    public function isDoSumUpWhitespace(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveSpacesBetweenTags(): bool;

    /**
     * @return bool
     */
    public function isDoOptimizeViaHtmlDomParser(): bool;

    /**
     * @return bool
     */
    public function isDoOptimizeAttributes(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveComments(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveWhitespaceAroundTags(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveOmittedQuotes(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveOmittedHtmlTags(): bool;

    /**
     * @return bool
     */
    public function isDoRemoveHttpPrefixFromAttributes(): bool;

    /**
     * @return array
     */
    public function getDomainsToRemoveHttpPrefixFromAttributes(): array;
}
