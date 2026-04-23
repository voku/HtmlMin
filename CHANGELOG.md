# Changelog

## 5.0.0 (2026-04-23)

- add `doRemoveCommentsOnly()` for removing regular HTML comments without applying other minification changes
- add opt-in inline JavaScript minification via `doMinifyJavaScript()` (uses `tedivm/jshrink`)
- add `doRemoveDataAttributes()` option to drop all `data-*` attributes (disabled by default)
- expand `doRemoveOmittedHtmlTags()` to cover more WHATWG optional-tag rules (`html`, `head`, `body`, `colgroup`, `tbody`, `rt`, newer `p` followers, and `hr` handling for `option` / `optgroup`)
- improve same-domain URL handling: `doMakeSameDomainsLinksRelative()` now accepts a plain `true` flag; use the new fluent `setLocalDomains(array)` helper to configure domains separately
- fix PHP 8.5 deprecation warnings: replace `SplObjectStorage`-based observer registration with an array-keyed map to avoid duplicates
- fix optional-tag omission around comments, protected comments, autonomous custom elements, and blocked `body` starts (`meta`, `link`, `noscript`, `script`, `style`, `template`)
- fix optional-tag omission when preserving inter-tag whitespace or comments so table sections and sibling tags still round-trip to the same DOM
- fix `<script type="application/ld+json">` whitespace minification preserving valid JSON
- fix nested protected child-node restoration (nocompress blocks, special script tags)
- fix whitespace around inline tags (`<b>`, `<em>`, `<i>`, `<strong>`, `<u>`) not being preserved correctly
- fix standalone `<head>` and `<body>` fragment closing-tag omission
- fix UTF-8 non-breaking space characters being incorrectly stripped
- fix PHP 8.3 `id` attribute handling triggering a type error
- fix `text/html` script-template tags leaking internal simple_html_dom placeholders
- fix `xml:lang` attribute being lost and trim script-tag whitespace for PHP 7.x compatibility
- internal: extract `patchSimpleHtmlDomPlaceholders()` as an idempotent Reflection-based method for simple_html_dom v5 compatibility
- upgrade `voku/simple_html_dom` dependency to `^5.0`; add `tedivm/jshrink ^1.8.1`; require PHP `>=7.1`
- update CI matrix to cover PHP 7.1 – 8.5; refresh GitHub Actions (checkout v6, cache v5, upload-artifact v7, codecov v6)

## 4.5.1 (2024-05-25)

- "fix SimpleHtmlDom::__construct(): Argument #1 ($node) must be of type DOMNode, null given" (thanks @frugan-dev)

## 4.5.0 (2022-06-09)

- added possibility overwrite special script tags on minify process (thanks @hryvinskyi)
- fix some PHP 8.1 type errors

## 4.4.10 (2022-03-13)

- use a new version of "voku/simple_html_dom" (4.8.5)
- phpstan reported errors fixed

## 4.4.9 (2022-03-09)

- optimize regex for gigantic inputs
- use a new version of "voku/simple_html_dom" (4.8.4)

## 4.4.8 (2020-08-11) 

- remove content before "<!doctype.*>", otherwise DOMDocument cannot handle the input
- use a new version of "voku/simple_html_dom" (4.7.22)

## 4.4.7 (2020-08-11) 

- use a new version of "voku/simple_html_dom" (4.7.21)

## 4.4.6 (2020-08-08)

-  fix invalid input html

## 4.4.5 (2020-08-06)

- allow to configure special comments via "setSpecialHtmlComments()"

## 4.4.4 (2020-08-06)

- fix problems with self-closing-tags e.g. <wbr>

## 4.4.3 (2020-04-06)

- fix "domNodeClosingTagOptional()" -> fix logic of detecting next sibling dom node

## 4.4.2 (2020-04-06)

- fix "domNodeClosingTagOptional()" -> do not remove "</p>" if there is more content in the parent node

## 4.4.1 (2020-04-05) 

- use a new version of "voku/simple_html_dom" (4.7.16)

## 4.4.0 (2020-04-05)

- add support for removing more default attributes
- add "doRemoveDefaultTypeFromButton()"
- add "doRemoveDefaultMediaTypeFromStyleAndLinkTag()"
- add "doRemoveDeprecatedTypeFromStyleAndLinkTag()"
- add "overwriteTemplateLogicSyntaxInSpecialScriptTags()"
- use a new version of "voku/simple_html_dom" (4.7.15)

## 4.3.0 (2020-03-22)

- add "isHTML4()"
- add "isXHTML()"
- fix "remove deprecated script-mime-types"
- use a new version of "voku/simple_html_dom" (4.7.13)


## 4.2.0 (2020-03-06)

- add "doKeepHttpAndHttpsPrefixOnExternalAttributes(bool)": keep http:// and https:// prefix for external links | thanks @abuyoyo
- add "doMakeSameDomainsLinksRelative(string[] $localDomains)": make the local domains relative | thanks @abuyoyo
- optimized "optgroup"-html compressing
- use a new version of "voku/simple_html_dom" (4.7.12)


## 4.1.0 (2020-02-06)

- add "doRemoveHttpsPrefixFromAttributes()": remove optional "https:"-prefix from attributes (depends on "doOptimizeAttributes(true)")


## 4.0.7 (2019-11-18)

- fix: too many single white spaces are removed


## 4.0.6 (2019-10-27)

- fix: fix regex for self-closing tags
- optimize performance via "strpos" before regex


## 4.0.5 (2019-09-19)

- fix: protect "nocompress"-tags before notifying the Observer


## 4.0.4 (2019-09-17)

- fix: removing of dom elements


## 4.0.3

- fix: removing of "\</p>"-tags


## 4.0.2

- use new version of "voku/simple_html_dom"


## 4.0.1

- optimize unicode support
- fix: remove unnecessary \</source> closing tag #40
- fix: bad minify text/x-custom-template #38


## 4.0.0

- use interfaces in the "HtmlMinDom"-Observer

-> this is a BC, but you can simply replace this classes in your observer implementation:

---> "SimpleHtmlDom" with "SimpleHtmlDomInterface

---> "HtmlMin" with "HtmlMinInterface"   


## 3.1.8

- fix / optimize: "doRemoveOmittedQuotes" -> support for "\<html ⚡>" via SimpleHtmlDom


## 3.1.7

- fix: "'" && '"' in attributes


## 3.1.6

- fix: keep HTML closing tags in \<script> tags 


## 3.1.5

- fix: keep newlines in e.g. "pre"-tags
- fix: remove newlines from "srcset" and "sizes" attribute


## 3.1.4 (2019-02-28)

- fix: get parent node
- code-style: remove "true" && "false" if return type is bool


## 3.1.1 / 3.1.2 / 3.1.3 (2018-12-28)

- use new version of "voku/simple_html_dom"


## 3.1.0 (2018-12-27)

- add "HtmlMinDomObserverInterface" (+ HtmlMin as Observable)
- use phpcs fixer


## 3.0.6 (2018-12-01)

- implement the "\<nocompress>"-tag + tests


## 3.0.5 (2018-10-17)

- update vendor (voku/simple_html_dom >= v4.1.7) + fix entities (&lt;, &gt;)


## 3.0.4 (2018-10-07)

- update vendor (voku/simple_html_dom >= v4.1.6) + option for keep broken html


## 3.0.3 (2018-05-08)

- update vendor (voku/simple_html_dom >= v4.1.4)


## 3.0.2 (2018-02-12)

- fix regex for self-closing tags


## 3.0.1 (2017-12-29)

- update vendor (voku/simple_html_dom >= v4.1.3)


## 3.0.0 (2017-12-22)

- remove "Portable UTF-8" as required dependency

-> this is a breaking change, without any API changes


## 2.0.4 (2017-12-22)

- check if there was already whitespace e.g. from the content


## 2.0.3 (2017-12-22)

- fix "Minifier removes spaces between tags"
- fix "Multiple horizontal whitespace characters not collapsed"


## 2.0.2 (2017-12-10)

- try to fix "Minifier removes spaces between tags" v2
- disable "doRemoveWhitespaceAroundTags" by default


## 2.0.1 (2017-12-10)

- try to fix "Minifier removes spaces between tags" v1


## 2.0.0 (2017-12-03)

- drop support for PHP < 7.0
- use "strict_types"
- doRemoveOmittedQuotes() -> remove quotes e.g. class="lall" => class=lall
- doRemoveOmittedHtmlTags() -> remove ommitted html tags e.g. \<p>lall\</p> => \<p>lall 
