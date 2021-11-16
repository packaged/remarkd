<?php
namespace Packaged\Remarkd\Rules;

class KeyboardKey implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/{key (.+)}/U', function (array $matches) {
      return '<kbd>' . $matches[1] . '</kbd>';
    }, $text);
  }
}
