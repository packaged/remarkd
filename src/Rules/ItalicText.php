<?php
namespace Packaged\Remarkd\Rules;

class ItalicText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\/\/(.+?)\/\/|\*(.+?)\*|\s_(.+?)_[\s|$]/', function (array $matches) {
      return '<em>' . ($matches[3] ?? $matches[2] ?? $matches[1]) . '</em>';
    }, $text);
  }
}
