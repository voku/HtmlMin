<?php
use voku\helper\HtmlMin;
use voku\helper\UTF8;

/**
 * Class HtmlMinTest
 */
class HtmlMinTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var HtmlMin
   */
  private $compressor;

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
   * @return array
   */
  public function providerNewLinesTabsReturns()
  {
    return array(
        array(
            "<html>\r\t<body>\n\t\t<h1>hoi</h1>\r\n\t</body>\r\n</html>",
            '<html><body><h1>hoi</h1></body>' . "\n" . '</html>',
        ),
        array(
            "<html>\r\t<h1>hoi</h1>\r\n\t\r\n</html>",
            '<html><h1>hoi</h1></html>',
        ),
        array(
            "<html><p>abc\r\ndef</p></html>",
            "<html><p>abc\ndef</p></html>",
        ),
    );
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
   * @return array
   */
  public function providerMultipleSpaces()
  {
    return array(
        array(
            '<html>  <body>          <h1>h  oi</h1>                         </body></html>',
            '<html><body><h1>h oi</h1></body></html>',
        ),
        array(
            '<html>   </html>',
            '<html></html>',
        ),
        array(
            "<html><body>  pre \r\n  suf\r\n  </body>",
            "<html><body>  pre \r\n  suf\r\n  </body>",
        ),
    );
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
   * @return array
   */
  public function providerSpaceAfterGt()
  {
    return array(
        array(
            '<html> <body> <h1>hoi</h1>   </body> </html>',
            '<html><body><h1>hoi</h1></body></html>',
        ),
        array(
            '<html>  a',
            '<html>  a',
        ),
    );
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
   * @return array
   */
  public function providerSpaceBeforeLt()
  {
    return array(
        array(
            '<html> <body>   <h1>hoi</h1></body> </html> ',
            '<html><body><h1>hoi</h1></body></html>',
        ),
        array(
            'a     <html>',
            'a     <html>',
        ),
    );
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
   * @return array
   */
  public function providerTrim()
  {
    return array(
        array(
            '              ',
            '',
        ),
        array(
            ' ',
            '',
        ),
    );
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
    self::assertSame('<!DOCTYPE html><html><body><form>' . $expected . '</form></body></html>', $actual);
  }

  /**
   * @return array
   */
  public function providerBoolAttr()
  {
    return array(
        array(
            '<input type="checkbox" autofocus="autofocus" checked="true" />',
            '<input autofocus checked type="checkbox">',
        ),
        array(
            '<input type="checkbox" autofocus="autofocus" checked="checked">',
            '<input autofocus checked type="checkbox">',
        ),
        array(
            '<input type="checkbox" autofocus="" checked="">',
            '<input autofocus checked type="checkbox">',
        ),
        array(
            '<input type="checkbox" autofocus="" checked>',
            '<input autofocus checked type="checkbox">',
        ),
    );
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

  /**
   * @return array
   */
  public function providerSpecialCharacterEncoding()
  {
    return array(
        array(
            "<html>\r\n\t<body>\xc3\xa0</body>\r\n\t</html>",
            '<html><body>à</body></html>',
        ),
    );
  }

  /**
   * @dataProvider providerSpecialCharacterEncoding
   *
   * @param $input
   * @param $expected
   */
  public function testSpecialCharacterEncoding($input, $expected)
  {
    $actual = $this->compressor->minify($input);
    self::assertSame($expected, $actual);
  }

  public function testMinifySimple()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = '
    <html>
    <head>     </head>
    <body>
      <p id="text" class="foo">foo</p>  <br />  <ul > <li> <p class="foo">lall</p> </li></ul>
    </body>
    </html>
    ';

    $expected = '<html><head></head><body><p class="foo" id="text">foo</p><br><ul><li><p class="foo">lall</p></li></ul></body></html>';

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMinifyHlt()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = str_replace(array("\r\n", "\r", "\n"), "\n", UTF8::file_get_contents(__DIR__ . '/fixtures/hlt.html'));
    $expected = str_replace(
        array(
            "\r\n",
            "\r",
            "\n",
        ), "\n", UTF8::file_get_contents(__DIR__ . '/fixtures/hlt_result.html')
    );

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMinifyCodeTag()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents(__DIR__ . '/fixtures/code.html'));
    $expected = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents(__DIR__ . '/fixtures/code_result.html'));

    self::assertSame($expected, $htmlMin->minify($html));
  }

  public function testMinifyBase()
  {
    // init
    $htmlMin = new HtmlMin();

    $html = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents(__DIR__ . '/fixtures/base.html'));
    $expected = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents(__DIR__ . '/fixtures/base_result.html'));

    self::assertSame($expected, $htmlMin->minify($html));
  }
}
