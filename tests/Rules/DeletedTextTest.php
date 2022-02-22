<?php

namespace Rules;

use Packaged\Remarkd\Rules\DeletedText;
use PHPUnit\Framework\TestCase;

class DeletedTextTest extends TestCase
{
  public function testUnderlined()
  {
    $it = new DeletedText();
    $this->assertEquals('test~testing <del>test</del>', $it->apply('test~testing ~~test~~'));
  }
}
