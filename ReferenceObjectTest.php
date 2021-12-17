<?php

use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class ReferenceObjectTest extends TestCase
{
  public function testReferences()
  {
    $remarkd = new Remarkd();

    $markdown = <<<MARKDOWN
Here is some information, we sourced this at {ref, content="http://www.google.com"}.   For any more info, ask.

# References
{reflist}
MARKDOWN;

    $expect = <<<HTML
<p>Here is some information, we sourced this at <a name="remarkd-ref-1RM" href="#remarkd-ref-foot-1RM">[1]</a>.   For any more info, ask.</p><h1>References</h1><p><ol><li><a name="remarkd-ref-root-1RM" href="#remarkd-ref-foot-1RM">http://www.google.com</a></li></ol></p>
HTML;

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
