<?php
namespace Packaged\Remarkd\Rules;

class UnderlinedText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/___(.+?)___/', '<u>\1</u>', $text);
  }

}
