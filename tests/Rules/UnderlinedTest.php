<?php

namespace Rules;

use Packaged\Remarkd\Rules\UnderlinedText;
use PHPUnit\Framework\TestCase;

class UnderlinedTest extends TestCase
{
  public function testUnderlined()
  {
    $it = new UnderlinedText();
    $this->assertEquals('<u>test1</u> - <u>test2</u>', $it->apply('___test1___ - ___test2___'));
  }
}
