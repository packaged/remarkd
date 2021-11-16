<?php
namespace Packaged\Remarkd\Rules;

class TipText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\{(.*?)\}\((.*?)\)/', function ($matches) {
      return '<span class="tooltip" title="' . $matches[2] . '">' . $matches[1] . '</span>';
    }, $text);
  }
}
