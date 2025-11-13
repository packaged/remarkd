<?php
namespace Packaged\RemarkdExample;

use Cubex\Controller\Controller;
use Packaged\Context\Context;
use Packaged\Dispatch\ResourceManager;
use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Link;
use Packaged\Glimpse\Tags\Lists\ListItem;
use Packaged\Glimpse\Tags\Lists\UnorderedList;
use Packaged\Glimpse\Tags\Span;
use Packaged\Glimpse\Tags\Text\HeadingOne;
use Packaged\Glimpse\Tags\Text\HeadingTwo;
use Packaged\Glimpse\Tags\Text\Paragraph;
use Packaged\Glimpse\Tags\Text\PreFormattedText;
use Packaged\Remarkd\Modules\IncludeModule;
use Packaged\Remarkd\Parser;
use Packaged\Remarkd\Remarkd;
use Packaged\RemarkdExample\Layout\TestSuitePage;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Renderable;

class TestSuiteController extends Controller
{
  protected function _generateRoutes()
  {
    yield self::_route('{category}/{feature}', 'feature');
    yield self::_route('{category}', 'feature');
    return 'index';
  }

  protected function _prepareResponse(Context $c, $result, $buffer = null)
  {
    if($result instanceof Renderable || is_scalar($result))
    {
      return parent::_prepareResponse($c, (new \Packaged\RemarkdExample\Layout\Wrap())->setContent($result), $buffer);
    }
    return parent::_prepareResponse($c, $result, $buffer);
  }

  public function getIndex()
  {
    $rmpm = ResourceManager::alias('remarkd');
    $rmpm->requireCss('resources/css/remarkd.css');
    $rmpm->requireCss('resources/css/remarkd-theme.css');
    $rmpm->requireJs('resources/js/tabs.js');
    $rmpm->requireJs('resources/js/accordion.js');
    $rmpm->requireJs('resources/js/remarkd.js');

    $page = new TestSuitePage();
    $page->setTitle('Remarkd Test Suite - Feature Catalog');

    // Get all feature categories
    $categories = $this->_getFeatureCategories();

    $content = Div::create()
      ->addClass('test-suite-index')
      ->appendContent([
          HeadingOne::create('Remarkd Test Suite'),
          Paragraph::create(
            'This test suite demonstrates all available features of the Remarkd parser. Click on any feature to see the raw text and rendered HTML side by side.'
          ),
          $this->_buildCategoryList($categories),
        ]
      );

    $page->setSidebar($this->_buildCategoryList($categories));
    $page->setContent($content);
    return $page;
  }

  public function getFeature()
  {
    $rmpm = ResourceManager::alias('remarkd');
    $rmpm->requireCss('resources/css/remarkd.css');
    $rmpm->requireCss('resources/css/remarkd-theme.css');
    $rmpm->requireJs('resources/js/tabs.js');
    $rmpm->requireJs('resources/js/accordion.js');
    $rmpm->requireJs('resources/js/remarkd.js');

    $category = $this->routeData()->get('category') ?? '';
    $feature = $this->routeData()->get('feature') ?? '';

    $resDir = $this->getContext()->getProjectRoot() . '/resources/test-suite/';

    // If no feature specified, this is a category page
    if(empty($feature))
    {
      return $this->_handleCategoryPage($category, $resDir, $rmpm);
    }

    // Feature page - check for feature file
    $filePath = $resDir . $category . '/' . $feature . '.remarkd';

    if(!file_exists($filePath))
    {
      $page = new TestSuitePage();
      $page->setTitle('Feature Not Found');
      $page->setContent(
        Div::create()
          ->addClass('test-suite-error')
          ->appendContent(
            [
              HeadingOne::create('Feature Not Found'),
              Paragraph::create('The requested feature file could not be found.'),
              Link::create('/', '← Back to Test Suite'),
            ]
          )
      );
      return $page;
    }

    // Read the raw file
    $rawContent = file_get_contents($filePath);

    // Parse and render
    $remarkd = new Remarkd();
    $remarkd->ctx()->setProjectRoot($resDir);
    $remarkd->ctx()->setResourceRoot($resDir);
    $remarkd->registerModule(IncludeModule::create($remarkd, $resDir));

    $parser = new Parser(file($filePath), $remarkd);
    $doc = $parser->parse();
    $htmlContent = $doc->produceSafeHTML()->getContent();

    $page = new TestSuitePage();
    $page->setTitle($doc->title ?: ucfirst(str_replace('-', ' ', $feature)));

    $categories = $this->_getFeatureCategories();
    $page->setSidebar($this->_buildCategoryList($categories, $category, $feature));

    $content = Div::create()
      ->addClass('test-suite-feature')
      ->appendContent([
          Link::create('/', '← Back to Test Suite')
            ->addClass('back-link'),
          Div::create()
            ->addClass('feature-viewer')
            ->appendContent([
                Div::create()
                  ->addClass('source-panel')
                  ->appendContent([
                      HeadingTwo::create('Source (.remarkd)'),
                      PreFormattedText::create($rawContent)
                        ->addClass('source-code')->setId('current-remarkd-content'),
                    ]
                  ),
                Div::create()
                  ->addClass('output-panel')
                  ->appendContent([
                      HeadingTwo::create('PHP Output'),
                      Div::create(new SafeHtml($htmlContent))
                        ->addClass('remarkd remarkd-styled output-content'),
                    ]
                  ),
                Div::create()
                  ->addClass('js-output-panel')
                  ->setId('js-output-panel')
                  ->appendContent([
                      HeadingTwo::create('JavaScript Output'),
                      Div::create()
                        ->setId('js-output-content')
                        ->addClass('remarkd remarkd-styled output-content'),
                    ]
                  ),
              ]
            ),
        ]
      );

    $page->setContent($content);
    return $page;
  }

  protected function _getFeatureCategories()
  {
    return [
      'blocks'   => [
        'title'       => 'Blocks',
        'description' => 'Structural block elements',
        'features'    => [
          'admonitions'      => 'Admonitions (NOTE, TIP, WARNING, etc.)',
          'callouts'         => 'Callout Blocks',
          'code-blocks'      => 'Code Blocks',
          'example-blocks'   => 'Example Blocks',
          'listing-blocks'   => 'Listing Blocks',
          'literal-blocks'   => 'Literal Blocks',
          'sidebar-blocks'   => 'Sidebar Blocks',
          'tabs'             => 'Tab Blocks',
          'accordions'       => 'Accordion Blocks',
          'steps'            => 'Step Blocks',
          'comments'         => 'Comment Blocks',
          'ordered-lists'    => 'Ordered Lists',
          'unordered-lists'  => 'Unordered Lists',
          'definition-lists' => 'Definition Lists',
          'paragraphs'       => 'Paragraphs',
        ],
      ],
      'rules'    => [
        'title'       => 'Text Rules',
        'description' => 'Inline text formatting rules',
        'features'    => [
          'bold-italic'           => 'Bold and Italic',
          'monospaced'            => 'Monospaced Text',
          'underlined'            => 'Underlined Text',
          'deleted'               => 'Deleted Text',
          'highlighted'           => 'Highlighted Text',
          'quoted'                => 'Quoted Text',
          'subscript-superscript' => 'Subscript and Superscript',
          'inline-styles'         => 'Inline Styles',
          'keyboard-keys'         => 'Keyboard Keys',
          'emojis'                => 'Emojis',
          'typographic-symbols'   => 'Typographic Symbols',
          'checkboxes'            => 'Checkboxes',
        ],
      ],
      'objects'  => [
        'title'       => 'Objects',
        'description' => 'Special object macros',
        'features'    => [
          'links'           => 'Links',
          'images'          => 'Images',
          'videos'          => 'Videos',
          'anchors'         => 'Anchors',
          'references'      => 'References',
          'progress-meters' => 'Progress Meters',
          'line-breaks'     => 'Line Breaks',
        ],
      ],
      'sections' => [
        'title'       => 'Sections',
        'description' => 'Document structure and sections',
        'features'    => [
          'document-header'  => 'Document Header',
          'section-headings' => 'Section Headings',
          'nested-sections'  => 'Nested Sections',
          'section-ids'      => 'Section IDs',
        ],
      ],
      'modules'  => [
        'title'       => 'Modules',
        'description' => 'Extended functionality modules',
        'features'    => [
          'includes' => 'Include Module',
        ],
      ],
      'advanced' => [
        'title'       => 'Advanced',
        'description' => 'Advanced features and combinations',
        'features'    => [
          'attributes'      => 'Document Attributes',
          'complex-nesting' => 'Complex Nesting',
          'mixed-content'   => 'Mixed Content',
        ],
      ],
    ];
  }

  protected function _buildCategoryList($categories, $activeCategory = null, $activeFeature = null)
  {
    $list = UnorderedList::create()->addClass('feature-catalog');

    foreach($categories as $categoryKey => $category)
    {
      $categoryItem = ListItem::create()
        ->appendContent([
            Link::create('/' . $categoryKey, $category['title'])
              ->addClass('category-link')
              ->addClass($categoryKey === $activeCategory ? 'active' : ''),
            Span::create($category['description'])->addClass('category-description'),
          ]
        );

      if($categoryKey === $activeCategory && !empty($category['features']))
      {
        $featureList = UnorderedList::create()->addClass('feature-list');
        foreach($category['features'] as $featureKey => $featureTitle)
        {
          $featureItem = ListItem::create()
            ->appendContent(
              Link::create('/' . $categoryKey . '/' . $featureKey, $featureTitle)
                ->addClass('feature-link')
                ->addClass($featureKey === $activeFeature ? 'active' : '')
            );
          $featureList->addItem($featureItem);
        }
        $categoryItem->appendContent($featureList);
      }

      $list->addItem($categoryItem);
    }

    return $list;
  }

  protected function _handleCategoryPage($category, $resDir, $rmpm)
  {
    $categories = $this->_getFeatureCategories();

    if(!isset($categories[$category]))
    {
      $page = new TestSuitePage();
      $page->setTitle('Category Not Found');
      $page->setContent(
        Div::create()
          ->addClass('test-suite-error')
          ->appendContent(
            [
              HeadingOne::create('Category Not Found'),
              Paragraph::create('The requested category could not be found.'),
              Link::create('/', '← Back to Test Suite'),
            ]
          )
      );
      return $page;
    }

    $categoryInfo = $categories[$category];

    // Check for category.remarkd or category/index.remarkd
    $categoryFilePath = $resDir . $category . '.remarkd';
    $categoryIndexPath = $resDir . $category . '/index.remarkd';

    $filePath = null;
    $rawContent = null;

    if(file_exists($categoryFilePath))
    {
      $filePath = $categoryFilePath;
      $rawContent = file_get_contents($filePath);
    }
    else if(file_exists($categoryIndexPath))
    {
      $filePath = $categoryIndexPath;
      $rawContent = file_get_contents($filePath);
    }

    // Parse the category file if it exists
    $htmlContent = '';
    $docTitle = $categoryInfo['title'];

    if($filePath && $rawContent)
    {
      $remarkd = new Remarkd();
      $remarkd->ctx()->setProjectRoot($resDir);
      $remarkd->ctx()->setResourceRoot($resDir);
      $remarkd->registerModule(IncludeModule::create($remarkd, $resDir));

      $parser = new Parser(file($filePath), $remarkd);
      $doc = $parser->parse();
      $htmlContent = $doc->produceSafeHTML()->getContent();
      if($doc->title)
      {
        $docTitle = $doc->title;
      }
    }

    // Build feature list
    $featureList = UnorderedList::create()->addClass('category-feature-list');
    if(!empty($categoryInfo['features']))
    {
      foreach($categoryInfo['features'] as $featureKey => $featureTitle)
      {
        $featureItem = ListItem::create()
          ->appendContent(
            Link::create('/' . $category . '/' . $featureKey, $featureTitle)
              ->addClass('feature-link')
          );
        $featureList->addItem($featureItem);
      }
    }

    $page = new TestSuitePage();
    $page->setTitle($docTitle);
    $page->setSidebar($this->_buildCategoryList($categories, $category));

    // If we have source content, show it in side-by-side view
    if($rawContent)
    {
      $content = Div::create()
        ->addClass('test-suite-feature')
        ->appendContent([
            Link::create('/', '← Back to Test Suite')
              ->addClass('back-link'),
            Div::create()
              ->addClass('feature-viewer')
              ->appendContent([
                  Div::create()
                    ->addClass('source-panel')
                    ->appendContent([
                        HeadingTwo::create('Source (.remarkd)'),
                        PreFormattedText::create($rawContent)
                          ->addClass('source-code')->setId('current-remarkd-content'),
                      ]
                    ),
                  Div::create()
                    ->addClass('output-panel')
                    ->appendContent([
                        Div::create()
                          ->addClass('remarkd remarkd-styled output-content')
                          ->appendContent([
                              new SafeHtml($htmlContent),
                              // HeadingTwo::create('Available Features'),
                              //$featureList,
                            ]
                          ),
                      ]
                    ),
                ]
              ),
          ]
        );
    }
    else
    {
      // No category file - show simple category page with feature list
      $content = Div::create()
        ->addClass('test-suite-category')
        ->appendContent([
            Link::create('/', '← Back to Test Suite')
              ->addClass('back-link'),
            Div::create()
              ->addClass('category-content')
              ->appendContent([
                  HeadingOne::create($docTitle),
                  Paragraph::create($categoryInfo['description'] ?? ''),
                  HeadingTwo::create('Available Features'),
                  $featureList,
                ]
              ),
          ]
        );
    }

    $page->setContent($content);
    return $page;
  }
}

