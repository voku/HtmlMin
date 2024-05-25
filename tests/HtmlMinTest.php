<?php

use voku\helper\HtmlMin;

/**
 * Class HtmlMinTest
 *
 * @internal
 */
final class HtmlMinTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HtmlMin
     */
    private $compressor;

    public function testEmptyResult()
    {
        static::assertSame('', (new HtmlMin())->minify(null));
        static::assertSame('', (new HtmlMin())->minify(' '));
        static::assertSame('', (new HtmlMin())->minify(''));
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

    public function testIssue67()
    {
        $minifier = new HtmlMin();

        $origHtml = '<p data-foo="" class="b c  a">   </p><img   src="data:image/png;base64,' . str_repeat('3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL', 2000) . '" />';

        $expectd = '<p class="a b c" data-foo=""></p><img src=data:image/png;base64,' . str_repeat('3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL3dhAAAAAXNSR0IArs4c6QAAAARnQU1BiVBORw0KGgoAAAANSUhEUgAAA7EAAAJyCAYAAAFlL', 2000) . '>';

        $compressedHtml = $minifier->minify($origHtml);

        static::assertSame($expectd, $compressedHtml);
    }

    public function testIssue63()
    {
        $html = '
<p>
	foo <code>bar</code>. ZIiiii  zzz <code>1.1</code> Lorem ipsum dolor sit amet, consectetur adipiscing elit.
</p>
						
<p>
	<h3>Vestibulum eget velit arcu.</h3>

	Vestibulum eget velit arcu. Phasellus eget scelerisque dui, nec elementum ante. <code>aoaoaoao</code>
</p>
';

        $htmlMin = new voku\helper\HtmlMin();

        $compressedHtml = $htmlMin->minify($html);

        $expectd = '<p>foo <code>bar</code>. ZIiiii  zzz <code>1.1</code> Lorem ipsum dolor sit amet, consectetur adipiscing elit. <p><h3>Vestibulum eget velit arcu.</h3>

	Vestibulum eget velit arcu. Phasellus eget scelerisque dui, nec elementum ante. <code>aoaoaoao</code>';

        static::assertSame($expectd, $compressedHtml);
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
                '<html> a',
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
                '<html> a',
                '<html> a',
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
    
    /**
     * @dataProvider providerBoolAttr
     *
     * @param $input
     */
    public function testBoolAttr($input)
    {
        $minifier = new HtmlMin();

        $html = '<!doctype html><html><body><form>' . $input . '</form></body></html>';
        $expected = '<!DOCTYPE html><html><body><form><input autofocus checked type=checkbox></form>';

        $actual = $minifier->minify($html);
        static::assertSame($expected, $actual);

        // ---

        $html = '<html><body><form>' . $input . '</form></body></html>';
        $expected = '<html><body><form><input autofocus checked type=checkbox></form>';

        $actual = $minifier->minify($html);
        static::assertSame($expected, $actual);

        // ---

        $html = '<form>' . $input . '</form>';
        $expected = '<form><input autofocus checked type=checkbox></form>';

        $actual = $minifier->minify($html);
        static::assertSame($expected, $actual);
    }

    public function testSpecialScriptTag()
    {
        // init
        $html = '
                <!doctype html>
        <html lang="fr">
        <head>
            <title>Test</title>
        </head>
        <body>
            A Body
        
            <script id="elements-image-1" type="text/html">
                <div class="place badge-carte">Place du Village<br>250m - 2mn à pied</div>
                <div class="telecabine badge-carte">Télécabine du Chamois<br>250m - 2mn à pied</div>
                <div class="situation badge-carte"><img src="https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png" alt=""></div>
            </script>
            
            <script id="elements-image-2" type="text/html">
                <div class="place badge-carte">Place du Village<br>250m - 2mn à pied</div>
                <div class="telecabine badge-carte">Télécabine du Chamois<br>250m - 2mn à pied</div>
                <div class="situation badge-carte"><img src="https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png" alt=""></div>
            </script>
            
            <script class="foobar" type="text/html">
                <div class="place badge-carte">Place du Village<br>250m - 2mn à pied</div>
                <div class="telecabine badge-carte">Télécabine du Chamois<br>250m - 2mn à pied</div>
                <div class="situation badge-carte"><img src="https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png" alt=""></div>
            </script>
            <script class="foobar" type="text/html">
                <div class="place badge-carte">Place du Village<br>250m - 2mn à pied</div>
                <div class="telecabine badge-carte">Télécabine du Chamois<br>250m - 2mn à pied</div>
                <div class="situation badge-carte"><img src="https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png" alt=""></div>
            </script>
        </body>
        </html>
        ';

        $expected = '<!DOCTYPE html><html lang=fr><head><title>Test</title> <body> A Body <script id=elements-image-1 type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div> <div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script> <script id=elements-image-2 type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div> <div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script> <script class=foobar type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div> <div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script> <script class=foobar type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div> <div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script>';

        $htmlMin = new HtmlMin();

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", $html);
        $expected = \str_replace(["\r\n", "\r", "\n"], "\n", $expected);

        static::assertSame(\trim($expected), $htmlMin->minify($html));
    }

    public function testMinifyJsTagStuff()
    {
        $html = '<script type="text/javascript">alert("Hello");</script>';

        $expected = '<script>alert("Hello");</script>';

        $htmlMin = new HtmlMin();

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", $html);
        $expected = \str_replace(["\r\n", "\r", "\n"], "\n", $expected);

        static::assertSame(\trim($expected), $htmlMin->minify($html));
    }

    public function testMinifyBase()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes()
                ->setDomainsToRemoveHttpPrefixFromAttributes(['csszengarden.com']);

        $html = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base1.html')
        );
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base1_result.html')
        );

        static::assertSame(\trim($expected), $htmlMin->minify($html));

        // ---

        $html = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base2.html')
        );
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base2_result.html')
        );

        static::assertSame(\trim($expected), $htmlMin->minify($html));

        // ---

        $html = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base3.html')
        );
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base3_result.html')
        );

        static::assertSame(\trim($expected), $htmlMin->minify($html));

        // ---

        $html = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base4.html')
        );
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/base4_result.html')
        );

        static::assertSame($expected, $htmlMin->minify($html));
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
        User: User-\<wbr>u00d0\<wbr>u009f\<wbr>u00d0\<wbr>u009a\<wbr>User<br>
        <a></a>
        ';

        $expected = '<dl><dt>foo <dd><span class=bar></span></dl> User: User-\<wbr>u00d0\<wbr>u009f\<wbr>u00d0\<wbr>u009a\<wbr>User<br> <a></a>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
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

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
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

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testSelfClosingTagHr()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = '<p class="foo bar"><hr class="bar foo"> or <hr class=" bar  foo   "/> or <hr> or <hr /> or <hr/> or <hr   /></p>';

        $expected = '<p class="bar foo"><hr class="bar foo"> or <hr class="bar foo"> or <hr> or <hr> or <hr> or <hr>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testHtmlInAttribute()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = '<button type="button" id="rotate_crop" class="btn btn-primary" data-loading-text="<i class=\'fa fa-spinner fa-spin\'></i> Rotando..." style="">Rotar</button>';

        $expected = '<button class="btn btn-primary" data-loading-text="<i class=\'fa fa-spinner fa-spin\'></i> Rotando..." id=rotate_crop type=button>Rotar</button>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testDataJsonInHtml()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = '
        <html>
          <body>
            <div data-json=\'{"key":"value"}\'></div>
          </body>
        </html>';

        $expected = '<html><body><div data-json=\'{"key":"value"}\'></div>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testDoNotAddSpacesViaDoRemoveWhitespaceAroundTags()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveWhitespaceAroundTags(false);

        $html = '<span class="foo"><span title="bar"></span><span title="baz"></span><span title="bat"></span></span>';

        $expected = '<span class=foo><span title=bar></span><span title=baz></span><span title=bat></span></span>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );

        // ---

        $html = '<span class="title">
                1.
                <a>Foo</a>
            </span>';

        $expected = '<span class=title> 1. <a>Foo</a> </span>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );

        // ---

        $htmlMin->doRemoveWhitespaceAroundTags(true);

        $html = '<span class="foo"><span title="bar"></span><span title="baz"></span><span title="bat"></span></span>';

        $expected = '<span class=foo><span title=bar></span><span title=baz></span><span title=bat></span></span>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );

        // ---

        $html = '<span class="title">
                1.
                <a>Foo</a>
            </span>';

        $expected = '<span class=title> 1. <a>Foo</a></span>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );

        // ---

        $html = '  <span>foo</span>
                                                    <a href="bar">baz</a>
                                    <span>bat</span>
    ';

        $expected = '<span>foo</span> <a href=bar>baz</a> <span>bat</span>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );

        // ---

        $html = '<span>foo</span>                                         <span>bar</span>                                                                                                                         <a>baz</a>                                                                                 <a>bat</a>';

        $expected = '<span>foo</span> <span>bar</span> <a>baz</a> <a>bat</a>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testMinifyCodeTag()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", \file_get_contents(__DIR__ . '/fixtures/code.html'));
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/code_result.html')
        );

        static::assertSame(\trim($expected), $htmlMin->minify($html));
    }

    public function testMinifyHlt()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes()
                ->setDomainsToRemoveHttpPrefixFromAttributes(['henkel-lifetimes.de']);

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", \file_get_contents(__DIR__ . '/fixtures/hlt.html'));
        $expected = \str_replace(
            [
                "\r\n",
                "\r",
                "\n",
            ],
            "\n",
            \file_get_contents(__DIR__ . '/fixtures/hlt_result.html')
        );

        static::assertSame(\trim($expected), $htmlMin->minify($html, true));
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

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
        );
    }

    public function testCodeAndSpecialEncoding()
    {
        $html = '<pre class="line-numbers mb-0"><code class="language-php" id="code">&lt;?php if(!defined(\'NormanHuth\') &amp;&amp; NormanHuth!=\'Public\') die(\'Access denied\');' . "\r\n" . '</code></pre>';

        $expected = '<pre class="line-numbers mb-0"><code class="language-php" id="code">&lt;?php if(!defined(\'NormanHuth\') &amp;&amp; NormanHuth!=\'Public\') die(\'Access denied\');
</code></pre>';

        $htmlMin = new voku\helper\HtmlMin();

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testMultiCode()
    {
        $html = '<code>foo</code> and <code>bar</code>';

        $expected = '<code>foo</code> and <code>bar</code>';

        $htmlMin = new voku\helper\HtmlMin();

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testStrongTagsSpecial()
    {
        $html = '
        <!DOCTYPE html>
<html lang="fr">
<head><title>Test</title></head>
<body>
<p>Visitez notre boutique <strong>eBay</strong> : <a href="https://foo.bar/lall" target="_blank">https://foo.bar/lall</a></p>
<p><strong>ID Vintage</strong>, spécialiste de la vente de pièces et accessoires pour motos tout- terrain classiques :<a href="https://foo.bar/123" target="_blank">https://foo.bar/123</a></p>
<p>Magazine <strong>Café-Racer</strong> : <a href="https://foo.bar/321" target="_blank">https://foo.bar/321</a></p>
<p><strong>Julien Lecointe</strong> : <a href="https://foo.bar/123456" target="_blank">https://foo.bar/123456</a></p>
</body>
</html>';

        $expected = '<!DOCTYPE html><html lang=fr><head><title>Test</title> <body><p>Visitez notre boutique <strong>eBay</strong> : <a href=https://foo.bar/lall target=_blank>https://foo.bar/lall</a> <p><strong>ID Vintage</strong>, spécialiste de la vente de pièces et accessoires pour motos tout- terrain classiques :<a href=https://foo.bar/123 target=_blank>https://foo.bar/123</a> <p>Magazine <strong>Café-Racer</strong> : <a href=https://foo.bar/321 target=_blank>https://foo.bar/321</a> <p><strong>Julien Lecointe</strong> : <a href=https://foo.bar/123456 target=_blank>https://foo.bar/123456</a>';

        $htmlMin = new voku\helper\HtmlMin();

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testImageScrset()
    {
        $html = '
        <html lang="fr">
<head><title>Test</title></head>
<body>
<article class="row" itemscope itemtype="http://schema.org/Product">
<a href="https://www.gmp-classic.com/echappement_311_echappement-cafe-racer-bobber-classique-etc_paire-de-silencieux-type-megaton-lg-440-mm-__gmp11114.html" itemprop="url" tabindex="-1" class="product-image overlay col-sm-3">
    <img width="212" height="170"
         itemprop="image"
         srcset="http://cdn.gmp-classic.com/cache/images/product/5ee4535311159aaf1c4ae44fbebd83c2-p1000223_3800.jpg 768w,
                     https://cdn.gmp-classic.com/cache/images/product/82e8bafbecab56f932720490e7fc2f85-p1000223_3800.jpg 992w,
                     https://cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg 1200w"
         sizes="(max-width: 768x) 354px,
                            (max-width: 992px) 305px,
                            212px"
         src="https://cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg"
         class="img-responsive"
         alt="PAIRE DE SILENCIEUX  TYPE MEGATON Lg 440 mm">
</a>
</article>
</body>
</html>';

        $expected = '<html lang=fr><head><title>Test</title> <body><article class=row itemscope itemtype=http://schema.org/Product><a class="col-sm-3 overlay product-image" href=//www.gmp-classic.com/echappement_311_echappement-cafe-racer-bobber-classique-etc_paire-de-silencieux-type-megaton-lg-440-mm-__gmp11114.html itemprop=url tabindex=-1><img alt="PAIRE DE SILENCIEUX  TYPE MEGATON Lg 440 mm" class=img-responsive height=170 itemprop=image sizes="(max-width: 768x) 354px, (max-width: 992px) 305px, 212px" src=//cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg srcset="//cdn.gmp-classic.com/cache/images/product/5ee4535311159aaf1c4ae44fbebd83c2-p1000223_3800.jpg 768w, //cdn.gmp-classic.com/cache/images/product/82e8bafbecab56f932720490e7fc2f85-p1000223_3800.jpg 992w, //cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg 1200w" width=212> </a> </article>';

        $htmlMin = new voku\helper\HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doRemoveHttpsPrefixFromAttributes();

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testKeepWhitespaceInPreTags()
    {
        $html = '<pre>
foo
        bar
                zoo
</pre>';

        $expected = '<pre>
foo
        bar
                zoo
</pre>';

        $htmlMin = new voku\helper\HtmlMin();

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testOptGroup()
    {
        $html = '<select>
          <optgroup label="Gruppe 1">
            <option>Option 1.1</option>
          </optgroup> 
          <optgroup label="Gruppe 2">
            <option>Option 2.1</option>
            <option>Option 2.2</option>
          </optgroup>
          <optgroup label="Gruppe 3" disabled>
            <option>Option 3.1</option>
            <option>Option 3.2</option>
            <option>Option 3.3</option>
          </optgroup>
        </select>';

        $htmlMin = new voku\helper\HtmlMin();

        $expected = '<select><optgroup label="Gruppe 1"><option>Option 1.1 <optgroup label="Gruppe 2"><option>Option 2.1 <option>Option 2.2 <optgroup disabled label="Gruppe 3"><option>Option 3.1 <option>Option 3.2 <option>Option 3.3</select>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testTagsInsideJs()
    {
        $htmlWithJs = '<p>Text 1</p><script>$(".second-column-mobile-inner").wrapAll("<div class=\'collapse\' id=\'second-column\'></div>");</script><p>Text 2</p>';

        $htmlMin = new voku\helper\HtmlMin();
        $htmlMin->useKeepBrokenHtml(true);

        $expected = '<p>Text 1</p><script>$(".second-column-mobile-inner").wrapAll("<div class=\'collapse\' id=\'second-column\'><\/div>");</script><p>Text 2';

        static::assertSame($expected, $htmlMin->minify($htmlWithJs));
    }

    public function testHtmlInsideJavaScriptTemplates()
    {
        $html = '
<script type=text/html>
    <p>Foo</p>

    <div class="alert alert-success">
        Bar
    </div>
    
    {{foo}}
    
    {{bar}}
    
    {{hello}}
</script>
';

        $htmlMin = new voku\helper\HtmlMin();
        $htmlMin->overwriteTemplateLogicSyntaxInSpecialScriptTags(['{%']);

        $expected = '<script type=text/html><p>Foo <div class="alert alert-success"> Bar </div> {{foo}} {{bar}} {{hello}} </script>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testOverwriteSpecialScriptTags()
    {
        $html = <<<HTML
<!doctype html>
    <html lang="nl">
        <head></head>
        <body>
        <script type="text/x-custom">
        <ul class="prices-tier items">
          <% _.each(tierPrices, function(item, key) { %>
          <%  var priceStr = '<span class="price-container price-tier_price">'
                  + '<span data-price-amount="' + priceUtils.formatPrice(item.price, currencyFormat) + '"'
                  + ' data-price-type=""' + ' class="price-wrapper ">'
                  + '<span class="price">' + priceUtils.formatPrice(item.price, currencyFormat) + '</span>'
                  + '</span>'
              + '</span>'; %>
          <li class="item">
              <%= 'some text %1 %2'.replace('%1', item.qty).replace('%2', priceStr) %>
              <strong class="benefit">
                 save <span class="percent tier-<%= key %>">&nbsp;<%= item.percentage %></span>%
              </strong>
          </li>
          <% }); %>
        </ul>
        </script>
        <div data-role="tier-price-block">
            <div> Some Content </div>
        </div>
        </body>
</html>
HTML;
        $htmlMin = new voku\helper\HtmlMin();
        $htmlMin->overwriteSpecialScriptTags(['text/x-custom']);
        $expected = <<<HTML
<!DOCTYPE html><html lang=nl><head> <body><script type=text/x-custom>
        <ul class="prices-tier items">
          <% _.each(tierPrices, function(item, key) { %>
          <%  var priceStr = '<span class="price-container price-tier_price">'
                  + '<span data-price-amount="' + priceUtils.formatPrice(item.price, currencyFormat) + '"'
                  + ' data-price-type=""' + ' class="price-wrapper ">'
                  + '<span class="price">' + priceUtils.formatPrice(item.price, currencyFormat) + '</span>'
                  + '</span>'
              + '</span>'; %>
          <li class="item">
              <%= 'some text %1 %2'.replace('%1', item.qty).replace('%2', priceStr) %>
              <strong class="benefit">
                 save <span class="percent tier-<%= key %>">&nbsp;<%= item.percentage %></span>%
              </strong>
          </li>
          <% }); %>
        </ul>
        </script> <div data-role=tier-price-block><div> Some Content </div> </div>
HTML;


        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testHtmlClosingTagInSpecialScript()
    {
        $htmlMin = new \voku\helper\HtmlMin();
        $htmlMin->doOptimizeViaHtmlDomParser(true);
        $html = $htmlMin->minify('
        <script id="comment-loader" type="text/x-handlebars-template">
            <nocompress>
                <i class="fas fa-spinner fa-pulse"></i> Loading ... 
             </nocompress>
        </script>');

        $expected = '<script id=comment-loader type=text/x-handlebars-template><nocompress>
                <i class="fas fa-spinner fa-pulse"></i> Loading ... 
             </nocompress></script>';

        static::assertSame($expected, $html);
    }

    public function testKeepPTagIfNeeded()
    {
        $html = '
        <div class="rating">
            <p style="margin: 0;">
                <span style="width: 100%;"></span>
            </p>
        
            (2 reviews)
        </div>
        ';

        $htmlMin = new voku\helper\HtmlMin();
        $result = $htmlMin->minify($html);

        $expected = '<div class=rating><p style="margin: 0;"><span style="width: 100%;"></span> </p> (2 reviews) </div>';

        static::assertSame($expected, $result);
    }

    public function testKeepPTagIfNeeded2()
    {
        $html = '
        <div>
            <p>
                <span>First Paragraph</span>
            </p>
            Loose Text
            <p>Another Paragraph</p>
        </div>
        ';

        $htmlMin = new voku\helper\HtmlMin();
        $result = $htmlMin->minify($html);

        $expected = '<div><p><span>First Paragraph</span> </p> Loose Text <p>Another Paragraph </div>';

        static::assertSame($expected, $result);
    }

    public function testVueJsExample()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = '
    <select v-model="fiter" @change="getGraphData" :class="[\'c-chart__label\']" name="filter">
    </select>
    ';

        $expected = '<select :class="[\'c-chart__label\']" @change=getGraphData name=filter v-model=fiter></select>';

        static::assertSame($expected, $htmlMin->minify($html));
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

        /** @noinspection JSUndeclaredVariable */
        $expected = '</script> <script async src=cdnjs></script>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testContentBeforeDoctypeExample()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->useKeepBrokenHtml(true);

        $html = '<!-- === BEGIN TOP === --><!DOCTYPE html>
        <!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
        <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
        <!--[if !IE]><!-->
        <html prefix="og: http://ogp.me/ns#" lang="ru">
        <!--<![endif]-->
        <head>
        <!-- Title -->
        <title>test</title>
        </head>
        <body>lall</body></html>
        ';

        $expected = '<!DOCTYPE html><!--[if IE 8]> <html lang="en" class="ie8"> <![endif]--><!--[if IE 9]> <html lang="en" class="ie9"> <![endif]--><!--[if !IE]><!--><html prefix="og: http://ogp.me/ns#" lang=ru> <!--<![endif]--> <head><title>test</title> <body>lall';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testDoNotCompressTag()
    {
        $minifier = new HtmlMin();
        $html = $minifier->minify("<span>&lt;<br><nocompress><br>\n lall \n </nocompress></span>");

        $expected = "<span>&lt;<br><nocompress><br>\n lall \n </nocompress></span>";

        static::assertSame($expected, $html);
    }

    public function testDoNotDecodeHtmlEnteties()
    {
        $minifier = new HtmlMin();
        $html = $minifier->minify('<span>&lt;</span>');

        $expected = '<span>&lt;</span>';

        static::assertSame($expected, $html);
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
        $htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag(false); // remove "type=text/css" from all links and styles
        $htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag(false); // remove "media="all" from all links and styles
        $htmlMin->doRemoveDefaultTypeFromButton(false); // remove type="submit" from button tags
        $htmlMin->doRemoveEmptyAttributes(false);                  // remove some empty attributes
        $htmlMin->doRemoveHttpPrefixFromAttributes(false);         // remove optional "http:"-prefix from attributes
        $htmlMin->doRemoveValueFromEmptyInput(false);              // remove 'value=""' from empty <input>
        $htmlMin->doRemoveWhitespaceAroundTags(false);             // remove whitespace around tags
        $htmlMin->doSortCssClassNames(false);                      // sort css-class-names, for better gzip results
        $htmlMin->doSortHtmlAttributes(false);                     // sort html-attributes, for better gzip results
        $htmlMin->doSumUpWhitespace(false);                        // sum-up extra whitespace from the Dom

        $html = '
    <html ⚡>
    <head>     </head>
    <body>
      <p id="text" class="foo">
        foo
      </p>  <br />  <ul > <li> <p class="foo">lall</p> </li></ul>
    </body>
    </html>
    ';

        $expected = '<html ⚡><head> <body><p id=text class=foo>
        foo
      </p> <br> <ul><li><p class=foo>lall </ul>';

        static::assertSame(
            \str_replace(["\r\n", "\r", "\n"], "\n", $expected),
            \str_replace(["\r\n", "\r", "\n"], "\n", $htmlMin->minify($html))
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

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testSourceNotNeeded()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = "\r\n
        \t<audio>\r\n
        \t<source src=\"horse.ogg\" type=\"audio/ogg\">\r\n
        \t<source src=\"horse.mp3\" type=\"audio/mpeg\">\r\n
        \tYour browser does not support the audio element.\r\n
        \t</audio>
        ";

        $expected = '<audio><source src=horse.ogg type=audio/ogg><source src=horse.mp3 type=audio/mpeg> Your browser does not support the audio element. </audio>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testJavaScriptTemplateTag()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = "
            <!doctype html>
            <html lang=\"nl\">
                <head>
                </head>
              <body>
              
              <div class=\"price-box price-tier_price\" data-role=\"priceBox\" data-product-id=\"1563\" data-price-box=\"product-id-1563\">
              </div>
              
              <script type=\"text/x-custom-template\" id=\"tier-prices-template\">
                <ul class=\"prices-tier items\">
                    <% _.each(tierPrices, function(item, key) { %>
                    <%  var priceStr = '<span class=\"price-container price-tier_price\">'
                            + '<span data-price-amount=\"' + priceUtils.formatPrice(item.price, currencyFormat) + '\"'
                            + ' data-price-type=\"\"' + ' class=\"price-wrapper \">'
                            + '<span class=\"price\">' + priceUtils.formatPrice(item.price, currencyFormat) + '</span>'
                            + '</span>'
                        + '</span>'; %>
                    <li class=\"item\">
                        <%= 'some text %1 %2'.replace('%1', item.qty).replace('%2', priceStr) %>
                        <strong class=\"benefit\">
                           save <span class=\"percent tier-<%= key %>\">&nbsp;<%= item.percentage %></span>%
                        </strong>
                    </li>
                    <% }); %>
                </ul>
              </script>
              
              <div data-role=\"tier-price-block\"></div>
              
              </body>
            </html>
            ";

        $expected = '<!DOCTYPE html><html lang=nl><head> <body><div class="price-box price-tier_price" data-price-box=product-id-1563 data-product-id=1563 data-role=priceBox></div> <script id=tier-prices-template type=text/x-custom-template>
                <ul class="prices-tier items">
                    <% _.each(tierPrices, function(item, key) { %>
                    <%  var priceStr = \'<span class="price-container price-tier_price">\'
                            + \'<span data-price-amount="\' + priceUtils.formatPrice(item.price, currencyFormat) + \'"\'
                            + \' data-price-type=""\' + \' class="price-wrapper ">\'
                            + \'<span class="price">\' + priceUtils.formatPrice(item.price, currencyFormat) + \'</span>\'
                            + \'</span>\'
                        + \'</span>\'; %>
                    <li class="item">
                        <%= \'some text %1 %2\'.replace(\'%1\', item.qty).replace(\'%2\', priceStr) %>
                        <strong class="benefit">
                           save <span class="percent tier-<%= key %>">&nbsp;<%= item.percentage %></span>%
                        </strong>
                    </li>
                    <% }); %>
                </ul>
              </script> <div data-role=tier-price-block></div>';

        static::assertSame($expected, $htmlMin->minify($html));
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
        $htmlMin->doRemoveSpacesBetweenTags();                // remove spaces between tags

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

        $expected = '<html><head><body><p class=foo id=text> foo </p><br><ul><li><p class="foo foo2">lall</ul>';

        static::assertSame($expected, $htmlMin->minify($html));
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

        $expected = '<html><head> <body><p class=foo id=text>foo</p> <br> <ul><li><p class=foo>lall </ul> <ul><li>1 <li>2 <li>3</ul> <table><tr><th>1 <th>2 <tr><td>foo <td><dl><dt>Coffee <dd>Black hot drink <dt>Milk <dd>White cold drink</dl> </table>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testMinifyKeepWhitespace()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveWhitespaceAroundTags(false);

        $html = '<p><span class="label-icons">XXX</span> <span class="label-icons label-free">FREE</span> <span class="label-icons label-pro">PRO</span> <span class="label-icons label-popular">POPULAR</span> <span class="label-icons label-community">COMMUNITY CHOICE</span></p>';

        $expected = '<p><span class=label-icons>XXX</span> <span class="label-free label-icons">FREE</span> <span class="label-icons label-pro">PRO</span> <span class="label-icons label-popular">POPULAR</span> <span class="label-community label-icons">COMMUNITY CHOICE</span>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testHtmlAndCssEdgeCase()
    {
        // init
        $htmlMin = new HtmlMin();

        $html = '<style><!--
h1 {
    color: red;
}
--></style>';

        $expected = '<style><!--
h1 {
    color: red;
}
--></style>';

        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testHtmlWithSpecialHtmlComment()
    {
        // init
        $htmlMin = new HtmlMin();
        $htmlMin->setSpecialHtmlComments(['INT_SCRIPT'], ['END_INI_SCRIPT']);

        $html = '<p><!--INT_SCRIPT test1 --> lall <!-- test2 --></p> <!-- test2 END_INI_SCRIPT-->';

        $expected = '<p><!--INT_SCRIPT test1--> lall <!--test2 END_INI_SCRIPT-->';

        static::assertSame($expected, $htmlMin->minify($html));
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

        static::assertSame($expected, $htmlMin->minify($html));
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

        $expected = '<html><head></head> <body><p class="foo" id="text">foo</p> <br> <ul><li><p class="foo">lall</p> </li></ul> <ul><li>1</li> <li>2</li> <li>3</li></ul> <table><tr><th>1</th> <th>2</th></tr> <tr><td>foo</td> <td><dl><dt>Coffee</dt> <dd>Black hot drink</dd> <dt>Milk</dt> <dd>White cold drink</dd></dl> </td></tr></table></body></html>';

        static::assertSame($expected, $htmlMin->minify($html));
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

        $expected = '<!DOCTYPE html><html lang=de><head><meta charset=utf-8><meta content="width=device-width, initial-scale=1.0" name=viewport><title>aussagekräftiger Titel der Seite</title> <body><p>Sehen Sie sich den Quellcode dieser Seite an. <kbd>(Kontextmenu: Seitenquelltext anzeigen)</kbd>';

        $htmlMin = new HtmlMin();
        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testForBrokenHtml()
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
</html><whatIsThat>???</whatIsThat>';

        $expected = '<!DOCTYPE html><html lang=de><head><meta charset=utf-8><meta content="width=device-width, initial-scale=1.0" name=viewport><title>aussagekräftiger Titel der Seite</title> <body><p>Sehen Sie sich den Quellcode dieser Seite an. <kbd>(Kontextmenu: Seitenquelltext anzeigen)</kbd> <whatisthat>???</whatisthat>';

        $htmlMin = new HtmlMin();
        static::assertSame($expected, $htmlMin->minify($html));
    }

    /**
     * @dataProvider providerMultipleSpaces
     *
     * @param $input
     * @param $expected
     */
    public function testMultipleSpaces($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input);
        static::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerNewLinesTabsReturns
     *
     * @param $input
     * @param $expected
     */
    public function testNewLinesTabsReturns($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input);
        static::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerSpaceAfterGt
     *
     * @param $input
     * @param $expected
     */
    public function testSpaceAfterGt($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input);
        static::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerSpaceBeforeLt
     *
     * @param $input
     * @param $expected
     */
    public function testSpaceBeforeLt($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input);
        static::assertSame($expected, $actual, 'tested: ' . $input);
    }

    /**
     * @dataProvider providerSpecialCharacterEncoding
     *
     * @param $input
     * @param $expected
     */
    public function testSpecialCharacterEncoding($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input, true);
        static::assertSame($expected, $actual);
    }

    /**
     * @dataProvider providerTrim
     *
     * @param $input
     * @param $expected
     */
    public function testTrim($input, $expected)
    {
        $actual = (new HtmlMin())->minify($input);
        static::assertSame($expected, $actual);
    }

    public function testDoRemoveCommentsWithFalse()
    {
        $minifier = new HtmlMin();

        $minifier->doRemoveComments(false);

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

        $actual = $minifier->minify($html);

        $expectedHtml = <<<'HTML'
<!DOCTYPE html><html><head><title>Test</title> <body><!-- do not remove comment --> <hr> <!--
do not remove comment
-->
HTML;

        static::assertSame($expectedHtml, $actual);
    }

    public function testSelfClosingInput()
    {
        $html = '
        <div class="form-group col-xl-10">
            <label for="chars">Zeichen</label>
            <div class="input-group">
                <input type="text" id="chars" class="form-control" value="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789![]{}()%&*$#^<>~@|" aria-describedby="chars-refresh-icon">
                <div class="input-group-append cursor-pointer" id="chars-refresh">
                    <div class="input-group-text" id="chars-refresh-icon"><i class="fas fa-undo fa-fw"></i></div>
                </div>
            </div>
        </div>
        ';

        $expected = '<div class="col-xl-10 form-group"><label for=chars>Zeichen</label> <div class=input-group><input aria-describedby=chars-refresh-icon class=form-control id=chars type=text value="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789![]{}()%&*$#^<>~@|"> <div class="cursor-pointer input-group-append" id=chars-refresh><div class=input-group-text id=chars-refresh-icon><i class="fa-fw fa-undo fas"></i></div> </div></div></div>';

        $htmlMin = new HtmlMin();
        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testRemoveDeprecatedTypeFromScriptTag()
    {
        $html = '<script type="text/javascript">alert("Hello");</script>
                <script type="text/ecmascript" src="ecmascript.js"></script>';
        $expected = '<script>alert("Hello");</script><script src=ecmascript.js></script>';

        $htmlMin = new HtmlMin();
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<script type="text/javascript">alert("Hello");</script>
                <script type="text/ecmascript" src="ecmascript.js"></script>';
        $expected = '<script type=text/javascript>alert("Hello");</script><script src=ecmascript.js type=text/ecmascript></script>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveDeprecatedTypeFromScriptTag(false);
        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testRelativeLinks()
    {
        $html = '<a href="https://www.example.com">Just an example</a>';
        $expected = '<a href=/>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['www.example.com']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="www.example.com/">Just an example</a>';
        $expected = '<a href=/>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['https://www.example.com/']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="www.example.com/foo/bar">Just an example</a>';
        $expected = '<a href=/foo/bar>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['httpS://www.example.com/']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="www.example.com/foo/bar">Just an example</a><a href="www.google.com/foo/bar">Just an example v2</a>';
        $expected = '<a href=/foo/bar>Just an example</a><a href=/foo/bar>Just an example v2</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['httpS://www.example.com/', 'httpS://www.google.com/']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="HTTPS://www.example.com/foo/bar">Just an example</a>';
        $expected = '<a href=/foo/bar>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['www.Example.com']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="HTTPS://موقع.وزارة-الاتصالات.مصر/foo/bar">Just an example</a>';
        $expected = '<a href=/foo/bar>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['موقع.وزارة-الاتصالات.مصر']);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href=HTTPS://موقع.وزارة-الاتصالات.مصر/foo/bar target=_blank>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['موقع.وزارة-الاتصالات.مصر']);
        static::assertSame($html, $htmlMin->minify($html));

        // --

        $html = '<a href=HTTPS://موقع.وزارة-الاتصالات.مصر/foo/bar rel=external>Just an example</a>';

        $htmlMin = new HtmlMin();
        $htmlMin->doMakeSameDomainsLinksRelative(['موقع.وزارة-الاتصالات.مصر']);
        static::assertSame($html, $htmlMin->minify($html));
    }

    public function testdoKeepHttpAndHttpsPrefixOnExternalAttributes()
    {
        $html = '<a href="http://www.example.com/">No remove</a><img src="http://www.example.com/" />';
        $expected = '<a href=http://www.example.com/>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes();
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<html><head><link href="http://www.example.com/"></head><body><a href="http://www.example.com/">No remove</a><img src="http://www.example.com/" /></body></html>';
        $expected = '<html><head><link href=//www.example.com/><body><a href=http://www.example.com/>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes();
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a target="_blank" href="http://www.example.com/">No remove</a><img src="http://www.example.com/" />';
        $expected = '<a href=http://www.example.com/ target=_blank>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes();
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<html><head><link href="http://www.example.com/"></head><body><a target="_blank" href="http://www.example.com/">No remove</a><img src="http://www.example.com/" /></body></html>';
        $expected = '<html><head><link href=//www.example.com/><body><a href=http://www.example.com/ target=_blank>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes();
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a href="http://www.example.com/">No remove</a><img src="http://www.example.com/" />';
        $expected = '<a href=//www.example.com/>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(false);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<html><head><link href="http://www.example.com/"></head><body><a href="http://www.example.com/">No remove</a><img src="http://www.example.com/" /></body></html>';
        $expected = '<html><head><link href=//www.example.com/><body><a href=//www.example.com/>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(false);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<a target="_blank" href="http://www.example.com/">No remove</a><img src="http://www.example.com/" />';
        $expected = '<a href=http://www.example.com/ target=_blank>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(false);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = '<html><head><link href="http://www.example.com/"></head><body><a target="_blank" href="http://www.example.com/">No remove</a><img src="http://www.example.com/" /></body></html>';
        $expected = '<html><head><link href=//www.example.com/><body><a href=http://www.example.com/ target=_blank>No remove</a><img src=//www.example.com/>';

        $htmlMin = new HtmlMin();
        $htmlMin->doRemoveHttpPrefixFromAttributes();
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(false);
        static::assertSame($expected, $htmlMin->minify($html));
    }

    public function testNullParentNode()
    {
        $html = " <nocompress>foo</nocompress> ";
        $expected = "<nocompress>foo</nocompress>";
        
        $htmlMin = new HtmlMin();
        $htmlMin->doOptimizeViaHtmlDomParser(true);
        static::assertSame($expected, $htmlMin->minify($html));

        // --

        $html = "<><code>foo</code><>";
        $expected = "<code>foo</code>";

        $htmlMin = new HtmlMin();
        $htmlMin->doOptimizeViaHtmlDomParser(true);
        static::assertSame($expected, $htmlMin->minify($html));
    }
}
