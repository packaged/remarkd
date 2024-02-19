<?php
namespace Packaged\Remarkd\Rules;

class QuoteText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/\"\`([^\`]+)\`\"/', '&ldquo;\1&rdquo;', $text);
  }
}
