<?php
namespace Packaged\Remarkd\Rules;

class UnderlinedText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/___(.+?)___/', function (array $matches) {
      return '<u>' . $matches[1] . '</u>';
    }, $text);
  }

}
