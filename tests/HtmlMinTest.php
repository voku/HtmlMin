<?php

use voku\helper\HtmlMin;

/**
 * Class HtmlMinTest
 */
class HtmlMinTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @var HtmlMin
   */
  private $compressor;

  public function testEmpryResult()
  {
    self::assertSame('', $this->compressor->minify(null));
    self::assertSame('', $this->compressor->minify(' '));
    self::assertSame('', $this->compressor->minify(''));
  }

  /**
   * @return array
   */
  public function providerBoolAttr(): array
  {
    return [
        [
            '<input type="checkbox" autofocus="autofocus" checked="true" />',
        ],
        [
            '<input type="checkbox" autofocus="autofocus" checked="checked">',
        ],
        [
            '<input type="checkbox" autofocus="" checked="">',
        ],
        [
            '<input type="checkbox" autofocus="" checked>',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerMultipleSpaces(): array
  {
    return [
        [
            '<html>  <body>          <h1>h  oi</h1>                         </body></html>',
            '<html><body><h1>h oi</h1>',
        ],
        [
            '<html>   </html>',
            '<html>',
        ],
        [
            "<html><body>  pre \r\n  suf\r\n  </body></html>",
            '<html><body> pre suf',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerNewLinesTabsReturns(): array
  {
    return [
        [
            "<html>\r\t<body>\n\t\t<h1>hoi</h1>\r\n\t</body>\r\n</html>",
            '<html><body><h1>hoi</h1>',
        ],
        [
            "<html>\r\t<h1>hoi</h1>\r\n\t\r\n</html>",
            '<html><h1>hoi</h1>',
        ],
        [
            "<html><p>abc\r\ndef</p></html>",
            '<html><p>abc def',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerSpaceAfterGt(): array
  {
    return [
        [
            '<html> <body> <h1>hoi</h1>   </body> </html>',
            '<html><body><h1>hoi</h1>',
        ],
        [
            '<html>  a',
            '<html>  a',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerSpaceBeforeLt(): array
  {
    return [
        [
            '<html> <body>   <h1>hoi</h1></body> </html> ',
            '<html><body><h1>hoi</h1>',
        ],
        [
            'a     <html>',
            'a     <html>',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerSpecialCharacterEncoding(): array
  {
    return [
        [
            "
            <html>
              \r\n\t
              <body>
                <ul style=''>
                  <li style='display: inline;' class='foo'>
                    \xc3\xa0
                  </li>
                  <li class='foo' style='display: inline;'>
                    \xc3\xa1
                  </li>
                </ul>
              </body>
              \r\n\t
            </html>
            ",
            '<html><body><ul><li class=foo style="display: inline;"> à <li class=foo style="display: inline;"> á </ul>',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerTrim(): array
  {
    return [
        [
            '              ',
            '',
        ],
        [
            ' ',
            '',
        ],
    ];
  }

  public function setUp()
  {
    parent::setUp();
    $this->compressor = new HtmlMin();
  }

  public function tearDown()
  {
    unset($this->compressor);
  }

  /**
   * @dataProvider providerBoolAttr
   *
   * @param $input
   */
  public function testBoolAttr($input)
  {
    $html = '<!doctype html><html><body><form>' . $input . '</form></body></html>';
    $expected = '<!DOCTYPE html><html><body><form><input autofocus checked type=checkbox></form>';

    $actual = $this->compressor->minify($html);
    self::assertSame($expected, $actual);

    // ---

    $html = '<html><body><form>' . $input . '</form></body></html>';
    $expected = '<html><body><form><input autofocus checked type=checkbox></form>';

    $actual = $this->compressor->minify($html);
    self::assertSame($expected, $actual);

    // ---

    $html = '<form>' . $input . '</form>';
    $expected = '<form><input autofocus checked type=checkbox></form>';

    $actual = $this->compressor->minify($html);
    self::assertSame($expected, $actual);
  }

  public function testMinifyBase()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doRemoveHttpPrefixFromAttributes()
            ->setDomainsToRemoveHttpPrefixFromAttributes(['csszengarden.com']);

    $html = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base1.html'));
    $expected = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base1_result.html'));

    self::assertSame(trim($expected), $htmlMin->minify($html));

    // ---

    $html = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base2.html'));
    $expected = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base2_result.html'));

    self::assertSame(trim($expected), $htmlMin->minify($html));

    // ---

    $html = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base3.html'));
    $expected = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/base3_result.html'));

    self::assertSame(trim($expected), $htmlMin->minify($html));
  }

  public function testRemoveWhitespaceAroundTags()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doRemoveWhitespaceAroundTags(true);

    $html = '
        <dl>
            <dt>foo
            <dd><span class="bar"></span>
        </dl>
        <a></a>
    ';

    $expected = '<dl><dt>foo <dd><span class=bar></span></dl> <a></a>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $html = '
        <dl>
            <dt>foo</dt>
            <dd><span class="bar">&nbsp;</span></dd>
        </dl>
        <a></a>
    ';

    $expected = '<dl><dt>foo <dd><span class=bar>&nbsp;</span></dl> <a></a>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $htmlMin->doRemoveWhitespaceAroundTags(false);

    $html = '
        <dl>
            <dt>foo
            <dd><span class="bar"></span>
        </dl>
        <a></a>
    ';

    $expected = '<dl><dt>foo <dd><span class=bar></span> </dl> <a></a>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );
  }

  public function testSelfClosingTagHr()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '<p class="foo bar"><hr class="bar foo"> or <hr class=" bar  foo   "/> or <hr> or <hr /> or <hr/> or <hr   /></p>';

    $expected = '<p class="bar foo"><hr class="bar foo"> or <hr class="bar foo"> or <hr> or <hr> or <hr> or <hr>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

  }

  public function testDoNotAddSpacesViaDoRemoveWhitespaceAroundTags()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doRemoveWhitespaceAroundTags(false);

    $html = '<span class="foo"><span title="bar"></span><span title="baz"></span><span title="bat"></span></span>';

    $expected = '<span class=foo><span title=bar></span><span title=baz></span><span title=bat></span></span>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $html = '<span class="title">
                1.
                <a>Foo</a>
            </span>';

    $expected = '<span class=title> 1. <a>Foo</a> </span>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $htmlMin->doRemoveWhitespaceAroundTags(true);


    $html = '<span class="foo"><span title="bar"></span><span title="baz"></span><span title="bat"></span></span>';

    $expected = '<span class=foo><span title=bar></span><span title=baz></span><span title=bat></span></span>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $html = '<span class="title">
                1.
                <a>Foo</a>
            </span>';

    $expected = '<span class=title> 1. <a>Foo</a></span>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $html = '  <span>foo</span>
                                                    <a href="bar">baz</a>
                                    <span>bat</span>
    ';

    $expected = '<span>foo</span> <a href=bar>baz</a> <span>bat</span>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );

    // ---

    $html = '<span>foo</span>                                         <span>bar</span>                                                                                                                         <a>baz</a>                                                                                 <a>bat</a>';

    $expected = '<span>foo</span> <span>bar</span> <a>baz</a> <a>bat</a>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );
  }

  public function testMinifyCodeTag()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/code.html'));
    $expected = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/code_result.html'));

    self::assertSame(trim($expected), $htmlMin->minify($html));
  }

  public function testMinifyHlt()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doRemoveHttpPrefixFromAttributes()
            ->setDomainsToRemoveHttpPrefixFromAttributes(['henkel-lifetimes.de']);

    $html = str_replace(["\r\n", "\r", "\n"], "\n", file_get_contents(__DIR__ . '/fixtures/hlt.html'));
    $expected = str_replace(
        [
            "\r\n",
            "\r",
            "\n",
        ], "\n", file_get_contents(__DIR__ . '/fixtures/hlt_result.html')
    );

    self::assertSame(trim($expected), $htmlMin->minify($html, true));
  }

  public function testOptionsDomFalse()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doOptimizeViaHtmlDomParser(false);

    $html = '<p id="text" class="foo">
        foo
      </p>  <br />  <ul > <li> <p class="foo">lall</p> </li></ul>
    ';

    $expected = '<p id="text" class="foo">
        foo
      </p>  <br>  <ul> <li> <p class="foo">lall</p> </li></ul>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );
  }

  public function testVueJsExample()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '
    <select v-model="fiter" @change="getGraphData" :class="[\'c-chart__label\']" name="filter">
    </select>
    ';

    $expected = '<select v-model="fiter" @change="getGraphData" :class="[\'c-chart__label\']" name="filter">
    </select>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testBrokenHtmlExample()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->useKeepBrokenHtml(true);

    /* @noinspection JSUnresolvedVariable */
    /* @noinspection UnterminatedStatementJS */
    /* @noinspection BadExpressionStatementJS */
    /* @noinspection JSUndeclaredVariable */
    $html = '
    </script>
    <script async src="cdnjs"></script>
    ';

    $expected = '</script> <script async src=cdnjs></script>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testDoNotCompressTag()
  {
    $minifier = new HtmlMin();
    $html = $minifier->minify("<span>&lt;<br><nocompress><br>\n lall \n </nocompress></span>");

    $expected = "<span>&lt;<br><nocompress><br>\n lall \n </nocompress></span>";

    self::assertSame($expected, $html);
  }

  public function testDoNotDecodeHtmlEnteties()
  {
    $minifier = new HtmlMin();
    $html = $minifier->minify('<span>&lt;</span>');

    $expected = '<span>&lt;</span>';

    self::assertSame($expected, $html);
  }

  public function testOptionsFalse()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doOptimizeAttributes(false);                     // optimize html attributes
    $htmlMin->doRemoveComments(false);                         // remove default HTML comments
    $htmlMin->doRemoveDefaultAttributes(false);                // remove defaults
    $htmlMin->doRemoveDeprecatedAnchorName(false);             // remove deprecated anchor-jump
    $htmlMin->doRemoveDeprecatedScriptCharsetAttribute(false); // remove deprecated charset-attribute (the browser will use the charset from the HTTP-Header, anyway)
    $htmlMin->doRemoveDeprecatedTypeFromScriptTag(false);      // remove deprecated script-mime-types
    $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink(false); // remove "type=text/css" for css links
    $htmlMin->doRemoveEmptyAttributes(false);                  // remove some empty attributes
    $htmlMin->doRemoveHttpPrefixFromAttributes(false);         // remove optional "http:"-prefix from attributes
    $htmlMin->doRemoveValueFromEmptyInput(false);              // remove 'value=""' from empty <input>
    $htmlMin->doRemoveWhitespaceAroundTags(false);             // remove whitespace around tags
    $htmlMin->doSortCssClassNames(false);                      // sort css-class-names, for better gzip results
    $htmlMin->doSortHtmlAttributes(false);                     // sort html-attributes, for better gzip results
    $htmlMin->doSumUpWhitespace(false);                        // sum-up extra whitespace from the Dom

    $html = '
    <html>
    <head>     </head>
    <body>
      <p id="text" class="foo">
        foo
      </p>  <br />  <ul > <li> <p class="foo">lall</p> </li></ul>
    </body>
    </html>
    ';

    $expected = '<html><head> <body><p id=text class=foo>
        foo
      </p> <br> <ul><li><p class=foo>lall </ul>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );
  }

  public function testDisappearingWhitespaceBetweenDlAndA()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '
    <dl>
        <dt>foo
        <dd><span class="bar"></span>
    </dl>
    <a class="baz"></a>
    ';

    $expected = '<dl><dt>foo <dd><span class=bar></span> </dl> <a class=baz></a>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testOptionsTrue()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doOptimizeAttributes();                     // optimize html attributes
    $htmlMin->doRemoveComments();                         // remove default HTML comments
    $htmlMin->doRemoveDefaultAttributes();                // remove defaults
    $htmlMin->doRemoveDeprecatedAnchorName();             // remove deprecated anchor-jump
    $htmlMin->doRemoveDeprecatedScriptCharsetAttribute(); // remove deprecated charset-attribute (the browser will use the charset from the HTTP-Header, anyway)
    $htmlMin->doRemoveDeprecatedTypeFromScriptTag();      // remove deprecated script-mime-types
    $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink(); // remove "type=text/css" for css links
    $htmlMin->doRemoveEmptyAttributes();                  // remove some empty attributes
    $htmlMin->doRemoveHttpPrefixFromAttributes();         // remove optional "http:"-prefix from attributes
    $htmlMin->doRemoveValueFromEmptyInput();              // remove 'value=""' from empty <input>
    $htmlMin->doRemoveWhitespaceAroundTags();             // remove whitespace around tags
    $htmlMin->doSortCssClassNames();                      // sort css-class-names, for better gzip results
    $htmlMin->doSortHtmlAttributes();                     // sort html-attributes, for better gzip results
    $htmlMin->doSumUpWhitespace();                        // sum-up extra whitespace from the Dom

    $html = '
    <html>
    <head>     </head>
    <body>
      <p id="text" class="foo">
        foo
      </p>  <br />  <ul class="    " > <li> <p class=" foo  foo foo2 ">lall</p> </li></ul>
    </body>
    </html>
    ';

    $expected = '<html><head> <body><p class=foo id=text> foo </p> <br> <ul><li><p class="foo foo2">lall</ul>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMinifySimple()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '
    <html>
    <head>     </head>
    <body>
      <p id="text" class="foo">foo</p> 
      <br /> 
      <ul > <li> <p class="foo">lall</p> </li></ul>
      <ul>
        <li>1</li>
        <li>2</li>
        <li>3</li>
      </ul>
      <table>
        <tr>
          <th>1</th>
          <th>2</th>
        </tr>
        <tr>
          <td>foo</td>
          <td>
            <dl>
              <dt>Coffee</dt>
              <dd>Black hot drink</dd>
              <dt>Milk</dt>
              <dd>White cold drink</dd>
            </dl>
          </td>
        </tr>
      </table>
    </body>
    </html>
    ';

    $expected = '<html><head> <body><p class=foo id=text>foo</p> <br><ul><li><p class=foo>lall </ul><ul><li>1 <li>2<li>3</ul><table><tr><th>1 <th>2 <tr><td>foo <td><dl><dt>Coffee <dd>Black hot drink<dt>Milk<dd>White cold drink</dl> </table>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMultipleHorizontalWhitespaceCharactersCollaps()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '
    <form>
        <button>foo</button>
        <input type="hidden" name="bar" value="baz">
    </form>
    ';

    $expected = '<form><button>foo</button> <input name=bar type=hidden value=baz></form>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMinifySimpleWithoutOmittedTags()
  {
    // init
    $htmlMin = new HtmlMin();
    $htmlMin->doRemoveOmittedHtmlTags(false)
            ->doRemoveOmittedQuotes(false);

    $html = '
    <html>
    <head>     </head>
    <body>
      <p id="text" class="foo">foo</p> 
      <br /> 
      <ul > <li> <p class="foo">lall</p> </li></ul>
      <ul>
        <li>1</li>
        <li>2</li>
        <li>3</li>
      </ul>
      <table>
        <tr>
          <th>1</th>
          <th>2</th>
        </tr>
        <tr>
          <td>foo</td>
          <td>
            <dl>
              <dt>Coffee</dt>
              <dd>Black hot drink</dd>
              <dt>Milk</dt>
              <dd>White cold drink</dd>
            </dl>
          </td>
        </tr>
      </table>
    </body>
    </html>
    ';

    $expected = '<html><head></head> <body><p class="foo" id="text">foo</p> <br><ul><li><p class="foo">lall</p> </li></ul><ul><li>1</li> <li>2</li><li>3</li></ul><table><tr><th>1</th> <th>2</th></tr> <tr><td>foo</td> <td><dl><dt>Coffee</dt> <dd>Black hot drink</dd><dt>Milk</dt><dd>White cold drink</dd></dl> </td></tr></table></body></html>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testHtmlDoctype()
  {
    $html = '<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>aussagekräftiger Titel der Seite</title>
  </head>
  <body>
    <!-- Sichtbarer Dokumentinhalt im body -->
    <p>Sehen Sie sich den Quellcode dieser Seite an.
      <kbd>(Kontextmenu: Seitenquelltext anzeigen)</kbd></p>
  </body>
</html>';

    $expected = '<!DOCTYPE html><html lang=de><head><meta charset=utf-8> <meta content="width=device-width, initial-scale=1.0" name=viewport><title>aussagekräftiger Titel der Seite</title> <body><p>Sehen Sie sich den Quellcode dieser Seite an. <kbd>(Kontextmenu: Seitenquelltext anzeigen)</kbd>';

    $htmlMin = new HtmlMin();
    self::assertSame($expected, $htmlMin->minify($html));

  }

  /**
   * @dataProvider providerMultipleSpaces
   *
   * @param $input
   * @param $expected
   */
  public function testMultipleSpaces($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  /**
   * @dataProvider providerNewLinesTabsReturns
   *
   * @param $input
   * @param $expected
   */
  public function testNewLinesTabsReturns($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  /**
   * @dataProvider providerSpaceAfterGt
   *
   * @param $input
   * @param $expected
   */
  public function testSpaceAfterGt($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  /**
   * @dataProvider providerSpaceBeforeLt
   *
   * @param $input
   * @param $expected
   */
  public function testSpaceBeforeLt($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  /**
   * @dataProvider providerSpecialCharacterEncoding
   *
   * @param $input
   * @param $expected
   */
  public function testSpecialCharacterEncoding($input, $expected)
  {
    $actual = $this->compressor->minify($input, true);
    self::assertSame($expected, $actual);
  }

  /**
   * @dataProvider providerTrim
   *
   * @param $input
   * @param $expected
   */
  public function testTrim($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  public function testDoRemoveCommentsWithFalse()
  {
    $this->compressor->doRemoveComments(false);

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
</head>
<body>
<!-- do not remove comment -->
<hr />
<!--
do not remove comment
-->
</body>
</html>

HTML;

    $actual = $this->compressor->minify($html);

    $expectedHtml = <<<'HTML'
<!DOCTYPE html><html><head><title>Test</title> <body><!-- do not remove comment --> <hr><!--
do not remove comment
-->
HTML;

    self::assertSame($expectedHtml, $actual);
  }
}
