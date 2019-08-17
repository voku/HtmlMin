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

    public function testEmpryResult()
    {
        static::assertSame('', $this->compressor->minify(null));
        static::assertSame('', $this->compressor->minify(' '));
        static::assertSame('', $this->compressor->minify(''));
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

    protected function setUp()
    {
        parent::setUp();
        $this->compressor = new HtmlMin();
    }

    protected function tearDown()
    {
        $this->compressor = null;
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
        static::assertSame($expected, $actual);

        // ---

        $html = '<html><body><form>' . $input . '</form></body></html>';
        $expected = '<html><body><form><input autofocus checked type=checkbox></form>';

        $actual = $this->compressor->minify($html);
        static::assertSame($expected, $actual);

        // ---

        $html = '<form>' . $input . '</form>';
        $expected = '<form><input autofocus checked type=checkbox></form>';

        $actual = $this->compressor->minify($html);
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

        $expected = '<!DOCTYPE html><html lang=fr><head><title>Test</title> <body> A Body <script id=elements-image-1 type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div><div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script> <script id=elements-image-2 type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div><div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script><script class=foobar type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div><div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script><script class=foobar type=text/html><div class="badge-carte place">Place du Village<br>250m - 2mn à pied</div> <div class="badge-carte telecabine">Télécabine du Chamois<br>250m - 2mn à pied</div><div class="badge-carte situation"><img alt="" src=https://domain.tld/assets/frontOffice/kneiss/template-assets/assets/dist/img/08ecd8a.png></div></script>';

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

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", \file_get_contents(__DIR__ . '/fixtures/base1.html'));
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

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", \file_get_contents(__DIR__ . '/fixtures/base2.html'));
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

        $html = \str_replace(["\r\n", "\r", "\n"], "\n", \file_get_contents(__DIR__ . '/fixtures/base3.html'));
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

        $expected = '<!DOCTYPE html><html lang=fr><head><title>Test</title> <body><p>Visitez notre boutique <strong>eBay</strong> : <a href=https://foo.bar/lall target=_blank>https://foo.bar/lall</a> <p><strong>ID Vintage</strong>, spécialiste de la vente de pièces et accessoires pour motos tout- terrain classiques :<a href=https://foo.bar/123 target=_blank>https://foo.bar/123</a><p>Magazine <strong>Café-Racer</strong> : <a href=https://foo.bar/321 target=_blank>https://foo.bar/321</a><p><strong>Julien Lecointe</strong> : <a href=https://foo.bar/123456 target=_blank>https://foo.bar/123456</a>';

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
         srcset="https://cdn.gmp-classic.com/cache/images/product/5ee4535311159aaf1c4ae44fbebd83c2-p1000223_3800.jpg 768w,
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

        $expected = '<html lang=fr><head><title>Test</title> <body><article class=row itemscope itemtype=http://schema.org/Product><a class="col-sm-3 overlay product-image" href=https://www.gmp-classic.com/echappement_311_echappement-cafe-racer-bobber-classique-etc_paire-de-silencieux-type-megaton-lg-440-mm-__gmp11114.html itemprop=url tabindex=-1><img alt="PAIRE DE SILENCIEUX  TYPE MEGATON Lg 440 mm" class=img-responsive height=170 itemprop=image sizes="(max-width: 768x) 354px, (max-width: 992px) 305px, 212px" src=https://cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg srcset="https://cdn.gmp-classic.com/cache/images/product/5ee4535311159aaf1c4ae44fbebd83c2-p1000223_3800.jpg 768w, https://cdn.gmp-classic.com/cache/images/product/82e8bafbecab56f932720490e7fc2f85-p1000223_3800.jpg 992w, https://cdn.gmp-classic.com/cache/images/product/93c869f20df68d3e531f7e9c3e603e5e-p1000223_3800.jpg 1200w" width=212> </a> </article>';

        $htmlMin = new voku\helper\HtmlMin();

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
</script>
';

        $htmlMin = new voku\helper\HtmlMin();

        $expected = '<script type=text/html><p>Foo <div class="alert alert-success"> Bar </div></script>';

        static::assertSame($expected, $htmlMin->minify($html));
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

        $expected = '</script> <script async src=cdnjs></script>';

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

        $expected = '<html><head> <body><p class=foo id=text>foo</p> <br><ul><li><p class=foo>lall </ul><ul><li>1 <li>2<li>3</ul><table><tr><th>1 <th>2 <tr><td>foo <td><dl><dt>Coffee <dd>Black hot drink<dt>Milk<dd>White cold drink</dl> </table>';

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

        $expected = '<html><head></head> <body><p class="foo" id="text">foo</p> <br><ul><li><p class="foo">lall</p> </li></ul><ul><li>1</li> <li>2</li><li>3</li></ul><table><tr><th>1</th> <th>2</th></tr> <tr><td>foo</td> <td><dl><dt>Coffee</dt> <dd>Black hot drink</dd><dt>Milk</dt><dd>White cold drink</dd></dl> </td></tr></table></body></html>';

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

        $expected = '<!DOCTYPE html><html lang=de><head><meta charset=utf-8> <meta content="width=device-width, initial-scale=1.0" name=viewport><title>aussagekräftiger Titel der Seite</title> <body><p>Sehen Sie sich den Quellcode dieser Seite an. <kbd>(Kontextmenu: Seitenquelltext anzeigen)</kbd>';

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
        $actual = $this->compressor->minify($input);
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
        $actual = $this->compressor->minify($input);
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
        $actual = $this->compressor->minify($input);
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
        $actual = $this->compressor->minify($input);
        static::assertSame($expected, $actual);
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
        $actual = $this->compressor->minify($input);
        static::assertSame($expected, $actual);
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

        static::assertSame($expectedHtml, $actual);
    }
}
