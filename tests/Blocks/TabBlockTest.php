<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\TabBlock;
use Packaged\Remarkd\Rules\RuleEngine;
use PHPUnit\Framework\TestCase;

class TabBlockTest extends TestCase
{
  public function testBasic()
  {
    $tab = new TabBlock('{TAB, key=abc, name=TabTwo}');
    self::assertEquals('abc', $tab->key());
    self::assertEquals('TabTwo', $tab->name());

    $tab->addNewLine('Tab Content');
    $tab->addNewLine('{ENDTAB}');

    $re = new RuleEngine();
    $content = $tab->complete(new BlockEngine($re), $re);
    self::assertEquals('<div class="tab" data-tab-key="abc">Tab Content</div>', $content);
  }
}
