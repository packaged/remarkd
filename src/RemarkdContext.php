<?php
namespace Packaged\Remarkd;

use Packaged\Map\DataMap;
use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\ModuleBlock;
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

  /** @var \Packaged\Remarkd\Blocks\ModuleBlock */
  protected $_moduleBlock;

  protected $_meta = [];

  protected string $_projectRoot = '';

  public function __construct()
  {
    $this->_blockEngine = new BlockEngine($this);
    $this->_ruleEngine = new RuleEngine($this);
    $this->_objectEngine = new ObjectEngine($this);
    $this->_moduleBlock = new ModuleBlock();
    $this->_meta = new DataMap($this->_meta);
  }

  public function __clone()
  {
    $be = new BlockEngine($this);
    $be->setMatchers($this->_blockEngine->getMatchers());
    $this->setBlockEngine($be);
  }

  /**
   * @param \Packaged\Remarkd\Rules\RuleEngine $ruleEngine
   */
  public function setRuleEngine(RuleEngine $ruleEngine)
  {
    $this->_ruleEngine = $ruleEngine;
    return $this;
  }

  /**
   * @param \Packaged\Remarkd\Blocks\BlockEngine $blockEngine
   */
  public function setBlockEngine(BlockEngine $blockEngine)
  {
    $this->_blockEngine = $blockEngine;
    return $this;
  }

  /**
   * @param \Packaged\Remarkd\Objects\ObjectEngine $objectEngine
   */
  public function setObjectEngine(ObjectEngine $objectEngine)
  {
    $this->_objectEngine = $objectEngine;
    return $this;
  }

  /**
   * @param array|\Packaged\Map\DataMap $meta
   */
  public function setMeta($meta)
  {
    $this->_meta = $meta;
    return $this;
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

  public function modules(): ModuleBlock
  {
    return $this->_moduleBlock;
  }

  public function meta(): DataMap
  {
    return $this->_meta;
  }

  public function setProjectRoot(string $projectRoot): RemarkdContext
  {
    $this->_projectRoot = $projectRoot;
    return $this;
  }

  public function getProjectRoot(): string
  {
    return $this->_projectRoot;
  }
}
