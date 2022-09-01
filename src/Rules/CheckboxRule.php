<?php
namespace Packaged\Remarkd\Rules;

class CheckboxRule implements RemarkdRule
{
  public function apply(string $text): string
  {
    return str_replace(
      [
        '[ ]',
        '[x]',
        '[_]',
        '[*]',
      ],
      [
        '<input type="checkbox" readonly="readonly">',
        '<input type="checkbox" readonly="readonly" checked>',
        '<input type="checkbox">',
        '<input type="checkbox" checked>',
      ],
      $text
    );
  }
}
