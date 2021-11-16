<?php
namespace Packaged\Remarkd\Rules;

interface RemarkdownRule
{
  public function apply(string $text): string;
}
