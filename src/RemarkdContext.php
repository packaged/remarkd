<?php
namespace Packaged\Remarkd;

use Packaged\Map\DataMap;
use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\ModuleBlock;
use Packaged\Remarkd\Objects\ObjectEngine;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\Remarkd\Traits\TraitEngine;

class RemarkdContext
{
  /** @var RuleEngine */
  protected $_ruleEngine;

  /** @var BlockEngine */
  protected $_blockEngine;

  /** @var ObjectEngine */
  protected $_objectEngine;

  /** @var ModuleBlock */
  protected $_moduleBlock;

  /** @var TraitEngine */
  protected $_traitEngine;

  protected $_meta = [];

  protected string $_projectRoot = '';

  public function __construct()
  {
    $this->_blockEngine = new BlockEngine($this);
    $this->_ruleEngine = new RuleEngine($this);
    $this->_objectEngine = new ObjectEngine($this);
    $this->_moduleBlock = new ModuleBlock();
    $this->_traitEngine = new TraitEngine($this);
    $this->_meta = new DataMap($this->_meta);
  }

  public function __clone()
  {
    $be = new BlockEngine($this);
    $be->setMatchers($this->_blockEngine->getMatchers());
    $this->setBlockEngine($be);
  }

  /**
   * @param RuleEngine $ruleEngine
   *
   * @return RemarkdContext
   */
  public function setRuleEngine(RuleEngine $ruleEngine)
  {
    $this->_ruleEngine = $ruleEngine;
    return $this;
  }

  /**
   * @param BlockEngine $blockEngine
   *
   * @return RemarkdContext
   */
  public function setBlockEngine(BlockEngine $blockEngine)
  {
    $this->_blockEngine = $blockEngine;
    return $this;
  }

  /**
   * @param ObjectEngine $objectEngine
   *
   * @return RemarkdContext
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
   * @return RuleEngine
   */
  public function ruleEngine(): RuleEngine
  {
    return $this->_ruleEngine;
  }

  /**
   * @return BlockEngine
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

  public function setProjectRoot(string $root): self
  {
    $this->_projectRoot = $root;
    return $this;
  }

  public function getProjectRoot(): string
  {
    return $this->_projectRoot;
  }

  public function traitEngine(): TraitEngine
  {
    return $this->_traitEngine;
  }
}
