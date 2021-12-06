<?php
namespace Packaged\Remarkd\Rules;

class HighlightText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/!!([^!]+?)!!/', function (array $matches) {
      return '<mark class="highlight">' . $matches[1] . '</mark>';
    }, $text);
  }

}
