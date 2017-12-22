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