<?php
namespace Packaged\Remarkd\Rules;

class BoldText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\*\*(.+?)\*\*|__(.+?)__/', function (array $matches) {
      return '<strong>' . ($matches[2] ?? $matches[1]) . '</strong>';
    }, $text);
  }
}
