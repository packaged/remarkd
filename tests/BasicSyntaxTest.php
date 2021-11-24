<?php

use Packaged\Remarkd\Remarkd;
use PHPUnit\Framework\TestCase;

class BasicSyntaxTest extends TestCase
{
  // Best practices from https://www.markdownguide.org/basic-syntax/

  /**
   * @dataProvider providerHeaders
   * @dataProvider providerParagraphs
   * @dataProvider providerBlockQuotes
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

  public function providerParagraphs()
  {
    return [
      [
        'Two Paragraphs',
        'I really like using Markdown.

I think I\'ll use it to format all of my documents from now on .',
        '<p>I really like using Markdown.</p>'
        . '<p>I think I\'ll use it to format all of my documents from now on .</p>',
      ],
      [
        'Line Breaks',
        'This is the first line.
And this is the second line.',
        '<p>This is the first line.<br/>'
        . 'And this is the second line.</p>',
      ],
    ];
  }

  public function providerBlockQuotes()
  {
    return [
      [
        'Simple Block Quote',
        '> Dorothy followed her through many of the beautiful rooms in her castle.',
        '<blockquote><p>Dorothy followed her through many of the beautiful rooms in her castle.</p></blockquote>',
      ],
      [
        'Multi Line Block Quote',
        '> Dorothy followed her through many of the beautiful rooms in her castle.
>
> The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.',
        '<blockquote>'
        . '<p>Dorothy followed her through many of the beautiful rooms in her castle.</p>'
        . "\n"
        . '<p>The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.</p>'
        . '</blockquote>',
      ],
      [
        'Nested Blockquotes',
        '> Dorothy followed her through many of the beautiful rooms in her castle.
>
>> The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.',
        '<blockquote>'
        . '<p>Dorothy followed her through many of the beautiful rooms in her castle.</p>'
        . "\n"
        . '<blockquote>'
        . '<p>The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.</p>'
        . '</blockquote>'
        . '</blockquote>',
      ],
      [
        'Blockquotes with Other Elements',
        '> #### The quarterly results look great!
>
> - Revenue was off the chart.
> - Profits were higher than ever.
>
>  *Everything* is going according to **plan**.',
        '<blockquote>'
        . '<h4>The quarterly results look great!</h4>'
        . "\n\n"
        . '<ul>'
        . '<li>Revenue was off the chart.</li>'
        . '<li>Profits were higher than ever.</li>'
        . '</ul>'
        . "\n"
        . '<p><em>Everything</em> is going according to <strong>plan</strong>.</p>'
        . '</blockquote>',
      ],
    ];
  }
}
