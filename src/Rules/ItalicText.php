<?php
namespace Packaged\Remarkd\Rules;

class ItalicText implements RemarkdRule
{
  public function apply(string $text): string
  {
    $unconstrained = preg_replace('/(\_\_)([^\_]+?)(\_\_)/', '<em>\2</em>', $text);
    return preg_replace('/([^\w])\_([^_]+)\_/', '\1<em>\2</em>', $unconstrained);
  }
}
