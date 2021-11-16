<?php
namespace Packaged\Remarkd\Rules;

class MonospacedText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\B`(.+?)`/', function (array $matches) {
      return '<span class="monospace">' . ($matches[2] ?? $matches[1]) . '</span>';
    }, $text);
  }
}
