<?php
namespace Packaged\Remarkd\Rules;

class BoldText implements RemarkdRule
{
  public function apply(string $text): string
  {
    $unconstrained = preg_replace('/(\*\*)([^\*]+?)(\*\*)/', '<strong>\2</strong>', $text);
    return preg_replace('/\*([^*\n]+)\*/', '<strong>\1</strong>', $unconstrained);
  }
}
