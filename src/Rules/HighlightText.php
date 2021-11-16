<?php
namespace Packaged\Remarkd\Rules;

class HighlightText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/!!([^!]+?)!!/', function (array $matches) {
      return '<span class="highlight">' . $matches[1] . '</span>';
    }, $text);
  }

}
