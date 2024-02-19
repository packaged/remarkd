<?php
namespace Packaged\Tests\Remarkd\Blocks;

use Packaged\Remarkd\Parser;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class AdmonitionTest extends TestCase
{
  public function testBasic()
  {
    $markdown = [
      "[.content-heading]\n",
      "====\n",
      "## Everything Here\n",
      "====\n",
      "\n",
    ];

    $expect = <<<HTML
<div class="remarkd-section section--level0 section--with-content"><div class="content-heading example-block"><div class="content"><h2>Everything Here</h2></div></div></div>
HTML;

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $remarkd->applyDefaultBlocks($remarkd->ctx()->blockEngine());
    $remarkd->applyDefaultModules($remarkd->ctx()->modules());
    $remarkd->applyDefaultObjects($remarkd->ctx()->objectEngine());
    $remarkd->applyDefaultRules($remarkd->ctx()->ruleEngine());

    $parser = new Parser($markdown, $remarkd);

    self::assertEquals($expect, (string)$parser->parse(false)->produceSafeHTML());
  }

  public function testMulti()
  {
    $markdown = [
      "[.content-heading]\n",
      "====\n",
      "## Everything Here\n",
      "====\n",
      "\n",
      "content",
      "\n",
      "[.content-footer]\n",
      "====\n",
      "## Everything Footer\n",
      "[.something]\n",
      "another",
      "====\n",
      "\n",
    ];

    $expect = <<<HTML
<div class="remarkd-section section--level0 section--with-content">
<div class="content-heading example-block">
<div class="content">
<h2>Everything Here</h2>
</div>
</div>
<p>content</p>
<div class="content-footer example-block">
<div class="content">
<h2>Everything Footer</h2>
<p class="something">another</p>
</div>
</div>
</div>
HTML;

    $expect = str_replace("\n", "", $expect);

    $ctx = new RemarkdContext();
    $remarkd = new Remarkd($ctx);
    $remarkd->applyDefaultBlocks($remarkd->ctx()->blockEngine());
    $remarkd->applyDefaultModules($remarkd->ctx()->modules());
    $remarkd->applyDefaultObjects($remarkd->ctx()->objectEngine());
    $remarkd->applyDefaultRules($remarkd->ctx()->ruleEngine());

    $parser = new Parser($markdown, $remarkd);

    self::assertEquals($expect, (string)$parser->parse(false)->produceSafeHTML());
  }
}
