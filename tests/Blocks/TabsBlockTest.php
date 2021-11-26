<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\TabsBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class TabsBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = <<<MARKDOWN
{TABGROUP}
{TAB, name=T1, key=t-1, default}
Tab One
{ENDTAB}
{TAB, name=T2, key=t-2}
Tab Two
{ENDTAB}
{ENDTABGROUP}
MARKDOWN;

    $expect = <<<HTML
<div class="tab-group">
<ul class="tab-header"><li><a href="#" data-tab-focus-key="t-1" class="active">T1</a></li><li><a href="#" data-tab-focus-key="t-2">T2</a></li></ul>
<div class="tabs">
<div class="tab" data-tab-key="t-1">Tab One</div>
<div class="tab" data-tab-key="t-2">Tab Two</div>
</div>
</div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new TabsBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
