<?php
namespace Packaged\Remarkd\Rules;

class TypographicSymbolRule implements RemarkdRule
{
  public function apply(string $text): string
  {
    return str_replace(
      ['(c)', '(C)', '(r)', '(R)', '(tm)', '(TM)', '(p)', '(P)', '(+-)',],
      ['©', '©', '®', '®', '™', '™', '§', '§', '±',],
      $text
    );
  }
}
