<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\ParagraphBlock;
use Packaged\Remarkd\Remarkd;
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

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new ParagraphBlock());

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

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new ParagraphBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}