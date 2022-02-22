<?php
namespace Packaged\Remarkd\Rules;

class KeyboardKey implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/{key (.+?)}/', '<kbd>\1</kbd>', $text);
  }
}
