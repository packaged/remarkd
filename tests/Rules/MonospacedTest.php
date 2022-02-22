<?php

namespace Rules;

use Packaged\Remarkd\Rules\MonospacedText;
use PHPUnit\Framework\TestCase;

class MonospacedTest extends TestCase
{
  public function testMonospaced()
  {
    $it = new MonospacedText();
    $this->assertEquals('testing`test <span class="monospace">test</span>', $it->apply('testing`test `test`'));
  }
}
