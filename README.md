[![Build Status](https://travis-ci.org/voku/HtmlMin.svg?branch=master)](https://travis-ci.org/voku/HtmlMin)
[![Coverage Status](https://coveralls.io/repos/voku/HtmlMin/badge.svg?branch=master&service=github)](https://coveralls.io/github/voku/HtmlMin?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/HtmlMin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/HtmlMin/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/voku/HtmlMin/v/stable)](https://packagist.org/packages/voku/html-min) 
[![Total Downloads](https://poser.pugx.org/voku/HtmlMin/downloads)](https://packagist.org/packages/voku/html-min) 
[![Latest Unstable Version](https://poser.pugx.org/voku/HtmlMin/v/unstable)](https://packagist.org/packages/voku/html-min)
[![License](https://poser.pugx.org/voku/HtmlMin/license)](https://packagist.org/packages/voku/html-min)

# HTML Compressor and Minifier

## Description

HtmlMin is a fast and very easy to use PHP5.3+ library that minifies given HTML5 source by removing extra whitespaces, comments and other unneeded characters without breaking the content structure. As a result pages become smaller in size and load faster. It will also prepare the HTML for better gzip results, by re-ranging (sort alphabetical) attributes and css-class-names.

## Usage

```php
$html = '<html>\r\n\t<body>\xc3\xa0</body>\r\n\t</html>';
$htmlMin = new HtmlMin();
echo $htmlMin->minify($html); // '<html><body>à</body></html>'
```

## Unit Test

1) [Composer](https://getcomposer.org) is a prerequisite for running the tests.

```
composer install voku/html-min
```

2) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit
```

