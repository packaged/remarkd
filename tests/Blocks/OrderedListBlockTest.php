<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\OrderedListBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class OrderedListBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
1 Item One
MARKDOWN;

    $expect = <<<HTML
<ol><li>Item One</li></ol>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new OrderedListBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLine()
  {
    $markdown = <<<MARKDOWN
1. Item One
2. Item Two
3. Item Three
MARKDOWN;

    $expect = <<<HTML
<ol><li>Item One</li><li>Item Two</li><li>Item Three</li></ol>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new OrderedListBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
