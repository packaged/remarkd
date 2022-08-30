<?php
namespace Packaged\Remarkd\Rules;

class ItalicText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/([_])(.+?)\1/', '<em>\2</em>', $text);
  }
}
