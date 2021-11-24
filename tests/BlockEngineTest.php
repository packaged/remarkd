<?php

use Packaged\Remarkd\Blocks\BlockEngine;
use PHPUnit\Framework\TestCase;

class BlockEngineTest extends TestCase
{
  public function testTrimLeftSpace()
  {
    self::assertEquals("", BlockEngine::trimLeftSpace(""));
    self::assertEquals("", BlockEngine::trimLeftSpace(" "));
    self::assertEquals("", BlockEngine::trimLeftSpace("  "));
    self::assertEquals("Hi", BlockEngine::trimLeftSpace(" Hi"));
    self::assertEquals("Hi", BlockEngine::trimLeftSpace("  Hi"));
    self::assertEquals(" Hi", BlockEngine::trimLeftSpace("   Hi"));
    self::assertEquals("Hi", BlockEngine::trimLeftSpace("   Hi", 3));
  }
}
