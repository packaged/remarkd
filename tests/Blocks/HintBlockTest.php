<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\HintBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class HintBlockTest extends TestCase
{
  /**
   * @dataProvider hintStyleProvider
   */
  public function testBasic($style)
  {
    $styleL = strtolower($style);

    $markdown = <<<MARKDOWN
($style) Content
MARKDOWN;

    $expect = <<<HTML
<div class="hint-$styleL">Content</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new HintBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  /**
   * @dataProvider hintStyleProvider
   */
  public function testCaptioned($style)
  {
    $styleL = strtolower($style);

    $markdown = <<<MARKDOWN
$style: Content
MARKDOWN;

    $expect = <<<HTML
<div class="hint-$styleL"><strong class="hint-caption">$style</strong>Content</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new HintBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  /**
   * @dataProvider hintStyleProvider
   */
  public function testMultiLine($style)
  {
    $styleL = strtolower($style);

    $markdown = <<<MARKDOWN
$style| Line 1
$style| Line 2
MARKDOWN;

    $expect = <<<HTML
<div class="hint-$styleL">Line 1
<br/>Line 2</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new HintBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testMultiLineSwitch()
  {
    $markdown = <<<MARKDOWN
WARNING| Line 1
SUCCESS| Line 2
MARKDOWN;

    $expect = <<<HTML
<div class="hint-warning">Line 1</div><div class="hint-success">Line 2</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new HintBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  /**
   * @dataProvider hintStyleProvider
   */
  public function testCaptionedMultiLine($style)
  {
    $styleL = strtolower($style);

    $markdown = <<<MARKDOWN
$style: Content

$style: Content 2
MARKDOWN;

    $expect = <<<HTML
<div class="hint-$styleL"><strong class="hint-caption">$style</strong>Content</div><div class="hint-$styleL"><strong class="hint-caption">$style</strong>Content 2</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new HintBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function hintStyleProvider()
  {
    return [
      ['SUCCESS'],
      ['WARNING'],
      ['NOTE'],
      ['NOTICE'],
      ['IMPORTANT'],
      ['DANGER'],
      ['TIP'],
    ];
  }
}
