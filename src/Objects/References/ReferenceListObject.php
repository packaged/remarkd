<?php
namespace Packaged\Remarkd\Objects\References;

use Packaged\Remarkd\Objects\AbstractRemarkdObject;

class ReferenceListObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'reflist';
  }

  public function render(): string
  {
    if($this->_context)
    {
      $ol = [];
      foreach($this->_context->meta()->get(ReferenceObject::META_KEY, []) as $ref)
      {
        if($ref instanceof ContentReference)
        {
          $ol[] = '<li><a name="remarkd-ref-root-' . $ref->code . '" href="#remarkd-ref-foot-' . $ref->code . '">' . $ref->content . '</a></li>';
        }
      }
      if(!empty($ol))
      {
        return '<ol>' . implode('', $ol) . '</ol>';
      }
      return '';
    }
    return '[MISSING-CONTEXT]';
  }
}
