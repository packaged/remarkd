<?php
namespace Packaged\Remarkd\Rules;

class DeletedText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/~~(.+?)~~/', function (array $matches) {
      return '<del>' . $matches[1] . '</del>';
    }, $text);
  }

}
