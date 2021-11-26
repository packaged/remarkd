<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\ParagraphBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class ParagraphBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
Content
MARKDOWN;

    $expect = <<<HTML
<p>Content</p>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->setDefaultBlock(new ParagraphBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLine()
  {
    $markdown = <<<MARKDOWN
Content Line 1
Content Line 2

Content Line 3
MARKDOWN;

    $expect = <<<HTML
<p>Content Line 1<br/>Content Line 2</p><p>Content Line 3</p>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->setDefaultBlock(new ParagraphBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
