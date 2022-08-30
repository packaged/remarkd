<?php
namespace Packaged\Remarkd\Rules;

class LinkText implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback(
      '/((http|ftp|https|mailto):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]))(\[([^\]\n]+)\])?/',
      function ($input) {
        return '<a href="' . $input[1] . '">' . ($input[6] ?? $input[1]) . '</a>';
      },
      $text
    );
  }
}
