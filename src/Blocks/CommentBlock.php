<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\SafeHtml\SafeHtml;

class CommentBlock extends ContainerBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_tag = Div::class;
  protected $_closer = '////';
  protected $_match = '/^\/{4,10}$/';

  public function produceSafeHTML(): SafeHtml
  {
    return new SafeHtml('');
  }
}
