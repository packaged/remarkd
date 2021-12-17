<?php
namespace Packaged\Remarkd\Objects\References;

use Packaged\Remarkd\Objects\AbstractRemarkdObject;

class ReferenceObject extends AbstractRemarkdObject
{
  const META_KEY = 'references';

  public function getIdentifier(): string
  {
    return 'ref';
  }

  public function render(): string
  {
    if($this->_context)
    {
      $references = $this->_context->meta()->get(self::META_KEY, []);
      $ref = new ContentReference();
      $ref->num = count($references) + 1;
      $ref->code = $this->_config['code'] ?? $ref->num . 'RM';
      $ref->content = $this->_config['content'] ?? '';
      $references[] = $ref;
      $this->_context->meta()->set(self::META_KEY, $references);
      if($ref->content)
      {
        $this->_context->ruleEngine()->parse($ref->content);
      }

      return '<a name="remarkd-ref-' . $ref->code . '" href="#remarkd-ref-foot-' . $ref->code . '">[' . $ref->num . ']</a>';
    }
    return '[MISSING-CONTEXT]';
  }
}