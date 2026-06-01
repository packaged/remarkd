<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\Glimpse\Tags\Div;
use Packaged\Remarkd\RemarkdContext;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

class DelimitedTextBlock extends BasicBlock implements BlockMatcher
{
  protected $_closer = '____';
  protected $_lines = [];

  public function match($line, ?Block $parent): ?Block
  {
    if($line === '____')
    {
      return new static();
    }
    return null;
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if($line !== $this->closer())
    {
      $this->_lines[] = $line;
      $this->addChild($line);
    }
    return true;
  }

  protected function _produceElement(): HtmlElement
  {
    if($this->_attr && $this->_attr->position(0) === 'verse')
    {
      $content = implode(PHP_EOL, $this->_lines);
      $verse = new CustomHtmlTag('pre', new SafeHtml($content));
      $verse->addClass('verse-block');
      return $verse;
    }

    $content = trim(implode(' ', array_map('trim', $this->_lines)));
    $content = new SafeHtml($content);
    $quote = new CustomHtmlTag('blockquote', CustomHtmlTag::build('p', [], $content));

    if($this->_attr)
    {
      $attrs = array_map('trim', explode(',', $this->_attr->raw()));
      $cite = $attrs[1] ?? null;
      if(isset($attrs[2]))
      {
        $cite .= ', ' . $attrs[2];
      }
      if($cite)
      {
        $quote->appendContent(Div::create($cite)->addClass('attribution'));
      }
    }

    return $quote;
  }
}
