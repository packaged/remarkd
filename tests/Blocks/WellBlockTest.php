<?php
namespace Packaged\Tests\Remarkd\Blocks;

use Packaged\Remarkd\Blocks\WellBlock;
use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class WellBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
|| Well Content
MARKDOWN;

    $expect = <<<HTML
<div class="well">Well Content</div>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new WellBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLine()
  {
    $markdown = <<<MARKDOWN
|| Well Content Line 1
|| Well Content Line 2
|| Well Content Line 3
MARKDOWN;

    $expect = <<<HTML
<div class="well">Well Content Line 1<br/>Well Content Line 2<br/>Well Content Line 3</div>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new WellBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
  public function testMultiLineCleanExit()
  {
    $markdown = <<<MARKDOWN
|| Well Content Line 1
|| Well Content Line 2
Not Well Content Line 3
MARKDOWN;

    $expect = <<<HTML
<div class="well">Well Content Line 1<br/>Well Content Line 2</div>Not Well Content Line 3
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new WellBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
