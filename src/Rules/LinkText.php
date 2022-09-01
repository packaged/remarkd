<?php
namespace Packaged\Remarkd\Rules;

class LinkText implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback(
      '/([^="(])((http|ftp|https|mailto):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]))(\[([^\]\n]+)\])?/',
      function ($input) {
        return $input[1] . '<a href="' . $input[2] . '">' . ($input[7] ?? $input[2]) . '</a>';
      },
      $text
    );
  }
}
