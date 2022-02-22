<?php

namespace Rules;

use Packaged\Remarkd\Rules\BoldText;
use PHPUnit\Framework\TestCase;

class BoldTest extends TestCase
{
  public function testBold()
  {
    $it = new BoldText();
    $this->assertEquals('<strong>test1</strong> - <strong>test2</strong>', $it->apply('**test1** - **test2**'));
    $this->assertEquals('<strong>test1</strong> - <strong>test2</strong>', $it->apply('__test1__ - __test2__'));
    $this->assertEquals('_*test1*_ - _*test2*_', $it->apply('_*test1*_ - _*test2*_'));
  }
}
