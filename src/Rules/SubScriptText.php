<?php
namespace Packaged\Remarkd\Rules;

class SubScriptText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/~([^~]+?)~/', '<sub>\1</sub>', $text);
  }
}
