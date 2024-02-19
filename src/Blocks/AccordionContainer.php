<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Button;
use Packaged\Glimpse\Tags\Div;
use Packaged\Helpers\Arrays;
use Packaged\Helpers\Strings;
use Packaged\Ui\Html\HtmlElement;

class AccordionContainer extends BasicBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_closer = '_-_';
  protected $_closeOnEmpty = false;

  protected function _produceElement(): HtmlElement
  {
    $setActive = true;
    $content = [];
    foreach($this->children() as $child)
    {
      if($child instanceof AccordionBlock)
      {
        $name = Strings::titleize($child->tabID());
        if($child->_attr)
        {
          $name = $child->_attr->get('name', $name);
        }
        $link = Button::create($name);
        $link->addClass('accordion');

        $content[] = [
          $link,
          Div::create($child->_children)->addClass('panel'),
        ];
      }
    }

    return Div::create(
      Arrays::interleave(PHP_EOL, $content)
    )->addClass('accordion-container');
  }

}
