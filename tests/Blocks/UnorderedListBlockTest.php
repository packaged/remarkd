<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\UnorderedListBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class UnorderedListBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
- Item One
MARKDOWN;

    $expect = <<<HTML
<ul><li>Item One</li></ul>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new UnorderedListBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLine()
  {
    $markdown = <<<MARKDOWN
- Item One
- Item Two
- Item Three
MARKDOWN;

    $expect = <<<HTML
<ul><li>Item One</li><li>Item Two</li><li>Item Three</li></ul>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new UnorderedListBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
