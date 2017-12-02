<?php

use voku\helper\HtmlMin;
use voku\helper\UTF8;

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
  public function providerBoolAttr()
  {
    return [
        [
            '<input type="checkbox" autofocus="autofocus" checked="true" />',
            '<input autofocus checked type="checkbox">',
        ],
        [
            '<input type="checkbox" autofocus="autofocus" checked="checked">',
            '<input autofocus checked type="checkbox">',
        ],
        [
            '<input type="checkbox" autofocus="" checked="">',
            '<input autofocus checked type="checkbox">',
        ],
        [
            '<input type="checkbox" autofocus="" checked>',
            '<input autofocus checked type="checkbox">',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerMultipleSpaces()
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
            "<html><body>  pre \r\n  suf\r\n  </body>",
            '<html><body> pre suf',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerNewLinesTabsReturns()
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
  public function providerSpaceAfterGt()
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
  public function providerSpaceBeforeLt()
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
  public function providerSpecialCharacterEncoding()
  {
    return [
        [
            "<html>\r\n\t<body>\xc3\xa0</body>\r\n\t</html>",
            '<html><body>à',
        ],
    ];
  }

  /**
   * @return array
   */
  public function providerTrim()
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
   * @param $expected
   */
  public function testBoolAttr($input, $expected)
  {
    $actual = $this->compressor->minify('<!doctype html><html><body><form>' . $input . '</form></body></html>');
    $expected = '<html><body><form><input autofocus checked type=checkbox></input></form>';
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

    $html = str_replace(["\r\n", "\r", "\n"], "\n", UTF8::file_get_contents(__DIR__ . '/fixtures/hlt.html'));
    $expected = str_replace(
        [
            "\r\n",
            "\r",
            "\n",
        ], "\n", UTF8::file_get_contents(__DIR__ . '/fixtures/hlt_result.html')
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
      </p> <br> <ul><li><p class=foo>lall</ul>';

    self::assertSame(
        str_replace(["\r\n", "\r", "\n"], "\n", $expected),
        str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
    );
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
          <td>bar</td>
        </tr>
      </table>
    </body>
    </html>
    ';

    $expected = '<html><head> <body><p class=foo id=text>foo</p> <br> <ul><li><p class=foo>lall</ul> <ul><li>1 <li>2 <li>3</ul> <table><tr><th>1</th> <th>2</th> <tr><td>foo <td>bar</table>';

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
}
