<?php
namespace Packaged\Remarkd\Rules;

class DeletedText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/~~(.+?)~~/', '<del>\1</del>', $text);
  }
}
