<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Core\AbstractContainerTag;
use Packaged\Glimpse\Tags\Div;
use Packaged\Helpers\Arrays;
use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

class BasicBlock implements ISafeHtmlProducer, Block
{
  protected $_closed = false;
  protected $_closer;
  /** @var Attributes */
  protected $_attr;
  protected $_class = [];
  protected $_tag;
  protected $_title;
  protected $_allowChildren = true;
  protected $_closeOnEmpty;
  protected $_substrim = '';
  protected $_substrimLen;
  protected $_contentType;

  public function setCloseOnEmptyLine(bool $close)
  {
    $this->_closeOnEmpty = $close;
    return $this;
  }

  public function setContentType($type)
  {
    $this->_contentType = $type;
    return $this;
  }

  public function contentType(): string
  {
    return $this->_contentType ?? Block::TYPE_SIMPLE;
  }

  public function isContainer(): bool
  {
    return $this->_contentType == Block::TYPE_COMPOUND;
  }

  public function trimLeftLength(): int
  {
    return $this->_substrimLen ?: 0;
  }

  public function trimLeftStr(): string
  {
    return $this->_substrim;
  }

  protected function _setSubstrim(string $substrim)
  {
    $this->_substrim = $substrim;
    $this->_substrimLen = strlen($substrim);
    return $this;
  }

  public function setAttributes(Attributes $attributes)
  {
    $this->_attr = $attributes;
    return $this;
  }

  public function closer(): ?string
  {
    return $this->_closer;
  }

  public function isOpen(): bool
  {
    return !$this->_closed;
  }

  protected $_children = [];

  public function setTitle($title)
  {
    $this->_title = $title;
    return $this;
  }

  public function addClass($class)
  {
    $this->_class[] = $class;
    return $this;
  }

  public function setCloser($tag)
  {
    $this->_closer = $tag;
    return $this;
  }

  public function setTag($tag)
  {
    $this->_tag = $tag;
    return $this;
  }

  public function tag()
  {
    return $this->_tag;
  }

  public function setAllowChildren(bool $bool)
  {
    $this->_allowChildren = $bool;
    return $this;
  }

  public function allowChildren(): bool
  {
    return $this->_allowChildren && $this->contentType() != Block::TYPE_SIMPLE;
  }

  public function closesOnEmptyLine(): bool
  {
    if($this->_closeOnEmpty !== null)
    {
      return $this->_closeOnEmpty;
    }
    return empty($this->closer());
  }

  public function close(): array
  {
    $blockIDs = [];
    foreach($this->_children as $child)
    {
      if($child instanceof Block)
      {
        $blockIDs = array_merge($blockIDs, $child->close());
      }
    }
    $blockIDs[] = $this->closer();
    $this->_closed = true;
    return $blockIDs;
  }

  public function addChild($block)
  {
    $this->_children[] = $block;
    return $this;
  }

  /**
   * @param \Packaged\Remarkd\RemarkdContext $ctx
   * @param string                           $line
   *
   * true = line appended
   * false = block complete
   *
   * @return bool
   */
  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    $this->addChild($this->_formatLine($ctx, $line));
    return true;
  }

  protected function _formatLine(RemarkdContext $ctx, string $line)
  {
    if(in_array($this->contentType(), [Block::TYPE_RAW, Block::TYPE_VERBATIM]))
    {
      return $line;
    }
    return new SafeHtml($ctx->ruleEngine()->parse($line));
  }

  public function produceSafeHTML(): SafeHtml
  {
    $content = Arrays::interleave(PHP_EOL, $this->_children);
    if($this->allowChildren())
    {
      $content = Div::create($content)->addClass('content');
    }
    if($this->_title)
    {
      $content = [Div::create($this->_title)->addClass('title'), $content];
    }

    if($this->_tag)
    {
      $ele = $this->_tag::create($content);
    }
    else
    {
      $ele = AbstractContainerTag::create($content);
    }

    if($ele instanceof HtmlElement)
    {
      if($this->_attr)
      {
        $class = $this->_attr->position(0);
        if($class && $class[0] == '.')
        {
          $ele->addClass(substr($class, 1));
        }
        else if($class === 'source')
        {
          $ele->addClass('source-code');
        }
      }
      $ele->addClass(...$this->_class);
    }

    return $ele->produceSafeHTML();
  }

  public function children(): array
  {
    return $this->_children;
  }

  public function allowLine(string $line): ?bool
  {
    if($line === '' && $this->closesOnEmptyLine())
    {
      return false;
    }

    return $this->_substrimLen == 0 || substr($line, 0, $this->_substrimLen) === $this->_substrim;
  }
}
