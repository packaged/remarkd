<?php
namespace Packaged\Remarkd\Rules;

class MonospacedText implements RemarkdRule
{
  public function apply(string $text): string
  {
    $text = preg_replace_callback('/([^\`]|^)\`\`([^\`]+)\`\`/', function ($match) {
      return $match[1] . '<span class="monospace">' . htmlspecialchars($match[2]) . '</span>';
    }, $text);
    return preg_replace_callback('/([^\`]|^)\`([^\`\n]+)\`/', function ($match) {
      return $match[1] . '<span class="monospace">' . htmlspecialchars($match[2]) . '</span>';
    }, $text);
  }
}
