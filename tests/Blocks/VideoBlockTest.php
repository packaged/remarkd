<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\VideoBlock;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class VideoBlockTest extends TestCase
{
  public function testBasic()
  {
    $markdown = '{VIDEO, source=youtube, id=abcdefghi12}';

    $expect = <<<HTML
<div class="video-container"><iframe src="https://www.youtube.com/embed/abcdefghi12" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $ctx->blockEngine()->registerBlock(new VideoBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
