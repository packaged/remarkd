<?php

namespace Rules;

use Packaged\Remarkd\Rules\ItalicText;
use PHPUnit\Framework\TestCase;

class ItalicTest extends TestCase
{
  public function testItalic()
  {
    $it = new ItalicText();
    $this->assertEquals('<em>test1</em> - <em>test2</em>', $it->apply('_test1_ - _test2_'));
    $this->assertEquals('<em>test1</em> - <em>test2</em>', $it->apply('*test1* - *test2*'));
  }
}
