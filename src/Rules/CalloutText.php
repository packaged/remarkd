<?php
namespace Packaged\Remarkd\Rules;

class CalloutText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/\<(\d+|\d+\.\d+|\w)\>/', '<i class="conum" data-value="\1"></i>', $text);
  }
}
