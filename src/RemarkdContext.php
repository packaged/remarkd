<?php
namespace Packaged\Remarkd;

use Packaged\Map\DataMap;
use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Rules\RuleEngine;

class RemarkdContext
{
  /** @var \Packaged\Remarkd\Rules\RuleEngine */
  protected $_ruleEngine;

  /** @var \Packaged\Remarkd\Blocks\BlockEngine */
  protected $_blockEngine;

  protected $_meta = [];

  public function __construct()
  {
    $this->_blockEngine = new BlockEngine($this);
    $this->_ruleEngine = new RuleEngine($this);
    $this->_meta = new DataMap($this->_meta);
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

  public function meta(): DataMap
  {
    return $this->_meta;
  }
}
