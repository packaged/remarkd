<?php
namespace Packaged\Remarkd\Rules;

use Packaged\Remarkd\Attributes;

class InlineStyleText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/(\[[^\]]+\])##([^\#]+)##/', function ($match) {
      $attr = new Attributes($match[1]);
      return '<span class="' . $attr->position(0) . '">' . $match[2] . '</span>';
    }, $text);
  }
}
