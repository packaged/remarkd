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
   * @dataProvider providerOrderedLists
   * @dataProvider providerImages
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
      ['Heading 1', '# Heading level 1', '<h1 id="heading-level-1">Heading level 1</h1>'],
      ['Heading 2', '## Heading level 2', '<h2 id="heading-level-2">Heading level 2</h2>'],
      ['Heading 3', '### Heading level 3', '<h3 id="heading-level-3">Heading level 3</h3>'],
      ['Heading 4', '#### Heading level 4', '<h4 id="heading-level-4">Heading level 4</h4>'],
      ['Heading 5', '##### Heading level 5', '<h5 id="heading-level-5">Heading level 5</h5>'],
      ['Heading anchor', '# Heading test {#custom-anchor}', '<h1 id="custom-anchor">Heading test</h1>'],
      [
        'Line Split Headings',
        'Try to put a blank line before...

# Heading

...and after a heading.',
        '<p>Try to put a blank line before...</p><h1 id="heading">Heading</h1><p>...and after a heading.</p>',
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
        '<p>This is the first line. '
        . 'And this is the second line.</p>',
      ],
    ];
  }

  public function providerImages()
  {
    return [
      [
        'Simple Image',
        '![](path/to/image.jpg)',
        '<p><img src="path/to/image.jpg"/></p>',
      ],
      [
        'Basic Image',
        '![ALT TEST](path/to/image.jpg)',
        '<p><img src="path/to/image.jpg" alt="ALT TEST"/></p>',
      ],
      [
        'Full Image',
        '![ALT TEST](path/to/image.jpg "Title Here")',
        '<p><img src="path/to/image.jpg" alt="ALT TEST" title="Title Here"/></p>',
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
        . '<h4 id="the-quarterly-results-look-great">The quarterly results look great!</h4>'
        . "\n"
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

  public function providerOrderedLists()
  {
    return [
      [
        'Numbered List',
        '1. First item
2. Second item
3. Third item
4. Fourth item',
        '<ol>'
        . '<li>First item</li>'
        . '<li>Second item</li>'
        . '<li>Third item</li>'
        . '<li>Fourth item</li>'
        . '</ol>',
      ],
      [
        'Same Numbered List',
        '1. First item
1. Second item
1. Third item
1. Fourth item',
        '<ol>'
        . '<li>First item</li>'
        . '<li>Second item</li>'
        . '<li>Third item</li>'
        . '<li>Fourth item</li>'
        . '</ol>',
      ],
      [
        'Random Numbered List',
        '1. First item
8. Second item
3. Third item
5. Fourth item',
        '<ol>'
        . '<li>First item</li>'
        . '<li>Second item</li>'
        . '<li>Third item</li>'
        . '<li>Fourth item</li>'
        . '</ol>',
      ],
      [
        'Indented Numbered List',
        '1. First item
2. Second item
3. Third item
    1. Indented item
    2. Indented item
4. Fourth item',
        '<ol>'
        . '<li>First item</li>'
        . '<li>Second item</li>'
        . '<li>Third item'
        . '<ol>'
        . '<li>Indented item</li>'
        . '<li>Indented item</li>'
        . '</ol>'
        . '</li>'
        . '<li>Fourth item</li>'
        . '</ol>',
      ],
      [
        'Nested Code Block',
        '1. Open the file.
2. Find the following code block on line 21:
        <html>
          <head>
            <title>Test</title>
          </head>
        </html>
3. Update the title to match the name of your website.',
        '<ol>'
        . '<li>Open the file.</li>'
        . '<li>Find the following code block on line 21:'
        . '<code>&lt;html&gt;
  &lt;head&gt;
    &lt;title&gt;Test&lt;/title&gt;
  &lt;/head&gt;
&lt;/html&gt;</code>'
        . '</li>'
        . '<li>Update the title to match the name of your website.</li>'
        . '</ol>',
      ],
    ];
  }
}
