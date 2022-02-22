<?php

use Packaged\Remarkd\Objects\ProgressMeterObject;
use Packaged\Remarkd\RemarkdContext;
use PHPUnit\Framework\TestCase;

class ObjectEngineTest extends TestCase
{
  public function testObjectEngine()
  {
    $ctx = new RemarkdContext();
    $engine = $ctx->objectEngine();
    $engine->registerObject(new ProgressMeterObject());

    $output = $engine->parse(
      'Start {meter id=remarkd-meter-123 min=1 max=20 text="1 to 20" value=10 label="Progress"} End'
    );
    self::assertEquals(
      'Start <label for="remarkd-meter-123" class="remarkd-meter-label">Progress</label><meter id="remarkd-meter-123" class="remarkd-meter"  min="1" max="20" value="10"">1 to 20</meter> End',
      $output
    );
  }
}
