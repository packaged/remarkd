<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\HorizontalRule;
use Packaged\Glimpse\Tags\Text\CodeBlock;
use Packaged\Helpers\Strings;
use Packaged\Remarkd\Rules\RuleEngine;

class Parser
{
  protected $_raw;
  /**
   * @var Document
   */
  protected $_document;

  /**
   * @var Section
   */
  protected $_currentSection;

  protected $_matchers;
  /**
   * @var \Packaged\Remarkd\Rules\RuleEngine
   */
  protected $_ruleEngine;

  public function __construct(array $rawLines)
  {
    $this->_document = new Document();
    $this->_document->data = new DocumentData();

    $this->_matchers = [
      BlockMatcher::i('```')->setContinue('`')->setTag(CodeBlock::class)->setAllowChildren(false),
      BlockMatcher::i('...')->setContinue('.')->setTag(CodeBlock::class)->setAllowChildren(false),
      BlockMatcher::i('---')->setContinue('-')->setTag(Div::class)->setClass('listing-block'),
      BlockMatcher::i('====')->setContinue('=')->setTag(Div::class)->setClass('example-block'),
      BlockMatcher::i('****')->setContinue('*')->setTag(Div::class)->setClass('sidebar-block'),
      BlockMatcher::i('_|_')->setContinue('|_'),
      BlockMatcher::i('TIP:', true)->setAllowChildren(false)->setClass('tip-block'),
    ];

    $remark = new Remarkd();
    $this->_ruleEngine = $remark->ctx()->ruleEngine();

    $section = new Section();
    $this->_setActiveSection($section);
    $this->_document->sections[] = $section;

    $this->_raw = [];
    $appendNext = false;
    foreach($rawLines as $line)
    {
      $line = trim($line, "\r\n\0\x0B ");
      // If the line ends with a backslash, then it's a continuation of the previous line

      if($appendNext)
      {
        $line = rtrim(array_pop($this->_raw), '\\ ') . "\n" . substr($line, 0, -1);
      }

      $appendNext = substr($line, -1) == '\\';

      $this->_raw[] = $line;
    }
  }

  const EXPECT_TITLE = 1;
  const EXPECT_AUTHORS = 2;
  const EXPECT_REVISION = 3;
  const EXPECT_DOCUMENT = 4;

  public function parse(): Document
  {
    $attribute = $title = null;
    $expectAction = self::EXPECT_TITLE;
    foreach($this->_raw as $line)
    {
      $char1 = $line[0] ?? '';
      //Skip Comment Lines starting with //
      if(substr($line, 0, 3) == '// ')
      {
        continue;
      }

      if(empty($line) && $expectAction < self::EXPECT_DOCUMENT)
      {
        continue;
      }

      $line = $this->_document->data->replace($line);

      if($char1 === ':')
      {
        $expectAction = self::EXPECT_DOCUMENT;
        $this->_document->data->add($line);
        continue;
      }

      switch($expectAction)
      {
        case self::EXPECT_TITLE:
          if(substr($line, 0, 2) == '= ')
          {
            $this->_document->title = substr($line, 2);
            $expectAction = self::EXPECT_AUTHORS;
            continue 2;
          }
          else
          {
            $expectAction = self::EXPECT_DOCUMENT;
            break;
          }
        case self::EXPECT_AUTHORS:
          if(!empty($line))
          {
            $this->_setAuthors($line);
          }
          $expectAction = self::EXPECT_REVISION;
          continue 2;
        case self::EXPECT_REVISION:
          if(!empty($line))
          {
            $this->_setRevision($line);
          }
          $expectAction = self::EXPECT_DOCUMENT;
          continue 2;
        case $char1 == '[' && substr($line, -1) == ']':
          $attribute = new Attributes($line);
          continue 2;
        case $char1 == '.':
          $title = substr($line, 1);
          continue 2;
      }

      $this->_addLine($line, $title, $attribute);
      $attribute = $title = null;
    }
    return $this->_document;
  }

  protected function _setAuthors($authorsLine)
  {
    foreach(explode(';', $authorsLine) as $author)
    {
      $this->_document->authors[] = trim($author);
    }
  }

  protected function _setRevision($revision)
  {
    $doc = $this->_document;
    //revision number, revision date: revision remark
    [$doc->revisionNumber, $revisionData] = Strings::explode(',', $revision, null, 2);
    [$doc->revisionDate, $doc->revisionRemark] = Strings::explode(':', trim($revisionData), null, 2);
    $doc->revisionRemark = trim($doc->revisionRemark);
  }

  protected function _addLine($line, $title = null, ?Attributes $attribute = null)
  {
    switch(trim($line))
    {
      // Line Break
      case '---':
      case '- - -':
      case '***':
      case '* * *':
        $line = HorizontalRule::create();
        break;
      // Page Break
      case '<<<':
        $line = Div::create()->setAttribute('style', 'break-after:page');
        break;
      case ($line[0] ?? '') == '=':
        if(preg_match('/^([=]{2,6}) (.*)/', $line, $matches))
        {
          $level = strlen($matches[1]) - 1;
          if(!$this->_currentSection->hasChildren() && empty($this->_currentSection->title))
          {
            array_pop($this->_document->sections);
          }

          $newSection = new Section();
          $newSection->level = $level;
          $newSection->title = $matches[2];

          //Always add Level 0 and 1 to the doc root
          if($level < 2)
          {
            $this->_document->sections[] = $newSection;
          }
          else if($level > $this->_currentSection->level + 1)
          {
            //Error - cannot nest further
            return $this;
          }
          else if($level > $this->_currentSection->level)
          {
            $this->_currentSection->addChild($newSection);
          }
          else if($this->_currentSection->level == $level)
          {
            $this->_currentSection->parent->addChild($newSection);
          }
          else
          {
            $useSection = $this->_currentSection;
            do
            {
              $useSection = $useSection->parent;
            }
            while($useSection && $useSection->level >= $level);
            $useSection->addChild($newSection);
          }

          $this->_setActiveSection($newSection);
          return $this;
        }
    }

    $this->_addSectionLine($line, $title, $attribute);
    return $this;
  }

  protected function _setActiveSection(Section $section)
  {
    $this->_currentSection = $section;
    return $this;
  }

  public function _addSectionLine($line, $title = null, $attribute = null)
  {
    $this->_currentSection->addLine($this->_ruleEngine, $this->_matchers, $line, $title, $attribute);
    return $this;
  }
}
