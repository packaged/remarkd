<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\HeadingBlock;
use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class HeadingBlockTest extends TestCase
{
  public function levels()
  {
    return [
      [1],
      [2],
      [3],
      [4],
      [5],
      [6],
    ];
  }

  /**
   * @dataProvider levels
   */
  public function testBasic($level)
  {
    $markdown = str_repeat('#', $level) . ' Heading ' . $level;

    $expect = '<h' . $level . '>Heading ' . $level . '</h' . $level . '>';

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new HeadingBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
