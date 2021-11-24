<?php

use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class BasicSyntaxTest extends TestCase
{
  // Best practices from https://www.markdownguide.org/basic-syntax/

  /**
   * @dataProvider providerHeaders
   *
   * @param $testName
   * @param $markdown
   * @param $expected
   */
  public function testBestPractice($testName, $markdown, $expected)
  {
    $remarkd = new Remarkd();
    $result = $remarkd->parse($markdown);
    self::assertEquals($expected, $result, $testName);
  }

  public function providerHeaders()
  {
    return [
      ['Heading 1', '# Heading level 1', '<h1>Heading level 1</h1>'],
      ['Heading 2', '## Heading level 2', '<h2>Heading level 2</h2>'],
      ['Heading 3', '### Heading level 3', '<h3>Heading level 3</h3>'],
      ['Heading 4', '#### Heading level 4', '<h4>Heading level 4</h4>'],
      ['Heading 5', '##### Heading level 5', '<h5>Heading level 5</h5>'],
      [
        'Line Split Headings',
        'Try to put a blank line before...

# Heading

...and after a heading.',
        '<p>Try to put a blank line before...</p><h1>Heading</h1><p>...and after a heading.</p>',
      ],
    ];
  }
}
