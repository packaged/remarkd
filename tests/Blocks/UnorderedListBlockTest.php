<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\UnorderedListBlock;
use Packaged\Remarkd\Remarkd;
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

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new UnorderedListBlock());

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

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new UnorderedListBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
