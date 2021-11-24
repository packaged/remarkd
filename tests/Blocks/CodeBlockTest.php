<?php
namespace Blocks;

use Packaged\Remarkd\Blocks\CodeBlock;
use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class CodeBlockTest extends TestCase
{
  public function testBasicSpace()
  {
    $markdown = <<<MARKDOWN
    <div class="test">Code</div>
MARKDOWN;

    $expect = <<<HTML
<code>&lt;div class=&quot;test&quot;&gt;Code&lt;/div&gt;</code>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new CodeBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testSpace()
  {
    $markdown = <<<MARKDOWN
    a simple
      indented code block
MARKDOWN;

    $expect = <<<HTML
<code>a simple
  indented code block</code>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new CodeBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testBasicTab()
  {
    $markdown = "\t<div class=\"test\">Code</div>";

    $expect = <<<HTML
<code>&lt;div class=&quot;test&quot;&gt;Code&lt;/div&gt;</code>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new CodeBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testFenced()
  {
    $markdown = <<<MARKDOWN
```
code line 1
    code line 2
code line 3

code line 4
```
MARKDOWN;

    $expect = <<<HTML
<code>code line 1
    code line 2
code line 3

code line 4</code>
HTML;

    $remarkd = new Remarkd(false, false);
    $remarkd->blockEngine()->registerBlock(new CodeBlock());

    self::assertEquals($expect, $remarkd->parse($markdown));
  }

  public function testFencedHr()
  {
    $markdown = <<<MARKDOWN
-------------------

```
function f() {
  global $\$variable_variable;
}
```

MARKDOWN;

    $expect = <<<HTML
<hr><code>function f() {
  global $\$variable_variable;
}</code>
HTML;

    $remarkd = new Remarkd();
    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
