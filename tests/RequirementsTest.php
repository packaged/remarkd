<?php

use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequirementsTest extends TestCase
{
  #[DataProvider('featureProvider')]
  public function testSharedRequirementFixtures(string $feature, string $input, string $expected): void
  {
    $remarkd = new Remarkd();
    $remarkd->ctx()->setProjectRoot(dirname(__DIR__));

    self::assertSame($expected, $remarkd->parse($input), $feature);
  }

  public static function featureProvider(): iterable
  {
    $root = dirname(__DIR__) . '/requirements/features';
    $features = array_filter(glob($root . '/*') ?: [], 'is_dir');
    sort($features);

    foreach($features as $featureDir)
    {
      $inputFile = $featureDir . '/input.remarkd';
      $expectedFile = $featureDir . '/expected.html';

      if(!is_file($inputFile) || !is_file($expectedFile))
      {
        continue;
      }

      yield basename($featureDir) => [
        basename($featureDir),
        rtrim(file_get_contents($inputFile), "\r\n"),
        rtrim(file_get_contents($expectedFile), "\r\n"),
      ];
    }
  }

  #[DataProvider('documentHeaderProvider')]
  public function testDocumentHeaderMode(string $name, string $input): void
  {
    $expected = '<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>';

    self::assertSame($expected, (new Remarkd())->parse($input, true), $name);
  }

  public static function documentHeaderProvider(): iterable
  {
    yield 'attributes after title' => [
      'attributes after title',
      "= Document Title\n:product: Remarkd\n\nThis is {product}.",
    ];
    yield 'attributes after author and revision' => [
      'attributes after author and revision',
      "= Document Title\nJane Doe\nv1.0, 2026-06-01: Released\n:product: Remarkd\n\nThis is {product}.",
    ];
    yield 'comment before header' => [
      'comment before header',
      "// banner comment\n= Document Title\n:product: Remarkd\n\nThis is {product}.",
    ];
  }
}
