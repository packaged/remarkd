<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\BlockQuote;
use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class BlockQuoteTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
> Block Quote
MARKDOWN;

    $expect = <<<HTML
<blockquote>Block Quote</blockquote>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new BlockQuote());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLine()
  {
    $markdown = <<<MARKDOWN
> Blockquotes can also be nested...
>> ...by using additional greater-than signs right next to each other...
> > > ...or with spaces between arrows.
MARKDOWN;

    $expect = <<<HTML
<blockquote>Blockquotes can also be nested...
<blockquote>...by using additional greater-than signs right next to each other...
<blockquote>...or with spaces between arrows.</blockquote></blockquote></blockquote>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new BlockQuote());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
