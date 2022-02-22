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
<p>Here is some information, we sourced this at <sup class="reference"><a name="rmdref-bdy-1RM" href="#rmdref-ft-1RM">[1]</a></sup>.   For any more info, ask.</p><h1>References</h1><p><ol class="reference"><li id="rmdref-ft-1RM"><a href="#rmdref-bdy-1RM" class="reference-tobody">^</a> http://www.google.com</li></ol></p>
HTML;

    self::assertEquals($expect, $remarkd->parse($markdown));
  }
}
