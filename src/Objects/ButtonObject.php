<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Glimpse\Tags\Link;
use Packaged\Helpers\Strings;

class ButtonObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'button';
  }

  public function render(): string
  {
    $text = $this->_config->get('text', Strings::titleize($this->_key));
    $color = $this->_config->get('color', 'gray');
    $target = $this->_config->get('target');

    $link = Link::create($this->_config->get('href', '#' . $this->_key), $text)
      ->addClass('btn', 'btn--' . $color);

    if($target !== null)
    {
      $link->setAttribute('target', $target);
    }

    return $link->render();
  }
}
