<?php
namespace Packaged\Remarkd\Rules;

class ItalicText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\*(.+?)\*|\b_(.+)_\b/', function (array $matches) {
      return '<em>' . ($matches[2] ?? $matches[1]) . '</em>';
    }, $text);
  }
}
