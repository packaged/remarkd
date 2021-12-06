<?php
namespace Packaged\Remarkd\Rules;

interface RemarkdRule
{
  public function apply(string $text): string;
}
