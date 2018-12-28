# Changelog 3.1.2

- use new version of "voku/simple_html_dom" (again)


# Changelog 3.1.1

- use new version of "voku/simple_html_dom"


# Changelog 3.1.0

- add "HtmlMinDomObserverInterface" (+ HtmlMin as Observable)
- use phpcs fixer


# Changelog 3.0.6 (2018-12-01)

- implement the "<nocompress>"-tag + tests


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
- doRemoveOmittedHtmlTags() -> remove ommitted html tags e.g. <p>lall</p> => <p>lall 