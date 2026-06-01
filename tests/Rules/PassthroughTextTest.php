<?php
namespace Rules;

use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class PassthroughTextTest extends TestCase
{
  public function testPassMacro()
  {
    $remarkd = new Remarkd();

    self::assertStringContainsString(
      '<p>The text <u>underline me</u> is underlined.</p>',
      $remarkd->parse('The text pass:[<u>underline me</u>] is underlined.')
    );
  }

  public function testTriplePlus()
  {
    $remarkd = new Remarkd();

    self::assertStringContainsString(
      '<p>The text <u>underline me</u> is underlined.</p>',
      $remarkd->parse('The text +++<u>underline me</u>+++ is underlined.')
    );
  }
}
