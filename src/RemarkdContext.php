<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Markup\MarkupResource;
use Packaged\Remarkd\Objects\ObjectEngine;
use Packaged\Remarkd\Rules\RuleEngine;

class RemarkdContext
{
  /** @var \Packaged\Remarkd\Rules\RuleEngine */
  protected $_ruleEngine;
  /** @var \Packaged\Remarkd\Blocks\BlockEngine */
  protected $_blockEngine;
  /** @var \Packaged\Remarkd\Objects\ObjectEngine */
  protected $_objectEngine;

  /** @var \Packaged\Remarkd\Markup\MarkupResource[] */
  protected $_markupResources = [
    MarkupResource::TYPE_JS   => [],
    MarkupResource::TYPE_CSS  => [],
    MarkupResource::TYPE_HTML => [],
  ];

  public function __construct()
  {
    $this->_ruleEngine = new RuleEngine($this);
    $this->_blockEngine = new BlockEngine($this);
    $this->_objectEngine = new ObjectEngine($this);
  }

  /**
   * @return \Packaged\Remarkd\Rules\RuleEngine
   */
  public function ruleEngine(): RuleEngine
  {
    return $this->_ruleEngine;
  }

  /**
   * @return \Packaged\Remarkd\Blocks\BlockEngine
   */
  public function blockEngine(): BlockEngine
  {
    return $this->_blockEngine;
  }

  public function objectEngine(): ObjectEngine
  {
    return $this->_objectEngine;
  }

  public function addResource(MarkupResource $resource)
  {
    $this->_markupResources[$resource->type][$resource->key] = $resource;
    return $this;
  }

  public function resources($type)
  {
    return $this->_markupResources[$type] ?? [];
  }
}
