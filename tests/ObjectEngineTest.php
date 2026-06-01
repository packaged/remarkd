<?php

use Packaged\Remarkd\Objects\ButtonObject;
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
    $engine->registerObject(new ButtonObject());

    $output = $engine->parse(
      'Start {{meter id=remarkd-meter-123 min=1 max=20 text="1 to 20" value=10 label="Progress"}} End'
    );
    self::assertEquals(
      'Start <label for="remarkd-meter-123" class="remarkd-meter-label">Progress</label><meter id="remarkd-meter-123" class="remarkd-meter"  min="1" max="20" value="10"">1 to 20</meter> End',
      $output
    );

    self::assertEquals(
      'Start <a href="/download" class="btn btn--primary" target="_blank">Download</a> End',
      $engine->parse('Start {{button:download href=/download text=Download color=primary target=_blank}} End')
    );
  }
}
