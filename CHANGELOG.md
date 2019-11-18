# Changelog 4.0.7

- fix: too many single white spaces are removed


# Changelog 4.0.6

- fix: fix regex for self-closing tags
- optimize performance via "strpos" before regex


# Changelog 4.0.5

- fix: protect "nocompress"-tags before notifying the Observer


# Changelog 4.0.4

- fix: removing of dom elements


# Changelog 4.0.3

- fix: removing of "\</p>"-tags


# Changelog 4.0.2

- use new version of "voku/simple_html_dom"


# Changelog 4.0.1

- optimize unicode support
- fix: remove unnecessary \</source> closing tag #40
- fix: bad minify text/x-custom-template #38


# Changelog 4.0.0

- use interfaces in the "HtmlMinDom"-Observer

-> this is a BC, but you can simply replace this classes in your observer implementation:

---> "SimpleHtmlDom" with "SimpleHtmlDomInterface

---> "HtmlMin" with "HtmlMinInterface"   


# Changelog 3.1.8

- fix / optimize: "doRemoveOmittedQuotes" -> support for "\<html ⚡>" via SimpleHtmlDom


# Changelog 3.1.7

- fix: "'" && '"' in attributes


# Changelog 3.1.6

- fix: keep HTML closing tags in \<script> tags 


# Changelog 3.1.5

- fix: keep newlines in e.g. "pre"-tags
- fix: remove newlines from "srcset" and "sizes" attribute


# Changelog 3.1.4

- fix: get parent node
- code-style: remove "true" && "false" if return type is bool


# Changelog 3.1.1 / 3.1.2 / 3.1.3

- use new version of "voku/simple_html_dom"


# Changelog 3.1.0

- add "HtmlMinDomObserverInterface" (+ HtmlMin as Observable)
- use phpcs fixer


# Changelog 3.0.6 (2018-12-01)

- implement the "\<nocompress>"-tag + tests


# Changelog 3.0.5 (2018-10-17)

- update vendor (voku/simple_html_dom >= v4.1.7) + fix entities (&lt;, &gt;)


# Changelog 3.0.4 (2018-10-07)

- update vendor (voku/simple_html_dom >= v4.1.6) + option for keep broken html


# Changelog 3.0.3 (2018-05-08)

- update vendor (voku/simple_html_dom >= v4.1.4)


# Changelog 3.0.2 (2018-02-12)

- fix regex for self-closing tags


# Changelog 3.0.1 (2017-12-29)

- update vendor (voku/simple_html_dom >= v4.1.3)


# Changelog 3.0.0 (2017-12-22)

- remove "Portable UTF-8" as required dependency

-> this is a breaking change, without any API changes


# Changelog 2.0.4 (2017-12-22)

- check if there was already whitespace e.g. from the content


# Changelog 2.0.3 (2017-12-22)

- fix "Minifier removes spaces between tags"
- fix "Multiple horizontal whitespace characters not collapsed"


# Changelog 2.0.2 (2017-12-10)

- try to fix "Minifier removes spaces between tags" v2
- disable "doRemoveWhitespaceAroundTags" by default


# Changelog 2.0.1 (2017-12-10)

- try to fix "Minifier removes spaces between tags" v1


# Changelog 2.0.0 (2017-12-03)

- drop support for PHP < 7.0
- use "strict_types"
- doRemoveOmittedQuotes() -> remove quotes e.g. class="lall" => class=lall
- doRemoveOmittedHtmlTags() -> remove ommitted html tags e.g. \<p>lall\</p> => \<p>lall 
