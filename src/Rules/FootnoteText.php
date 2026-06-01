<?php
namespace Packaged\Remarkd\Rules;

class FootnoteText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace(
      '/footnote:\[([^\]]+)]/',
      '<sup class="footnote">$1</sup>',
      $text
    );
  }
}
