# Changelog 2.0.x

# Changelog 2.0.1 (2017-12-10)

* try to fix "Minifier removes spaces between tags"

# Changelog 2.0.0 (2017-12-03)

* drop support for PHP < 7.0
* use "strict_types"
* doRemoveOmittedQuotes() -> remove quotes e.g. class="lall" => class=lall
* doRemoveOmittedHtmlTags() -> remove ommitted html tags e.g. <p>lall</p> => <p>lall 