<?php
namespace Packaged\Remarkd\Rules;

class ItalicText implements RemarkdRule
{
  public function apply(string $text): string
  {
    $unconstrained = preg_replace('/(\_\_)([^\_]+?)(\_\_)/', '<em>\2</em>', $text);
    return preg_replace('/\_([\w\s\']+)\_/', '<em>\1</em>', $unconstrained);
  }
}
