<?php
namespace Packaged\Remarkd\Rules;

class BoldText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/(\*{2}|_{2})(.+?)\1/', '<strong>\2</strong>', $text);
  }
}
