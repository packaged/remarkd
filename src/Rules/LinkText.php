<?php
namespace Packaged\Remarkd\Rules;

class LinkText implements RemarkdRule, ProtectorAware
{
  protected $_protect;

  public function setProtector(?callable $protect)
  {
    $this->_protect = $protect;
  }

  protected function _shield($value)
  {
    return $this->_protect ? ($this->_protect)($value) : $value;
  }

  public function applymd(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback(
      '/\[([^\]]*)]\(([^\)]*)\)/',
      function ($input) {
        return '<a href="' . $this->_shield($input[2]) . '">' . $input[1] . '</a>';
      },
      $text
    );
  }

  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback(
      '/([^="(])((http|ftp|https|mailto):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]))(\[([^\]\n]+)\])?/',
      function ($input) {
        return $input[1] . '<a href="' . $this->_shield($input[2]) . '">' . (empty($input[7]) ? $this->_shield($input[2]) : $input[7]) . '</a>';
      },
      $this->applymd($text)
    );
  }
}
