// Shared feature catalogue for the Remarkd documentation site.
//
// Each entry mirrors a fixture in requirements/features/<slug>/:
//   source   == input.remarkd
//   expected == expected.html
//
// This data drives both the Feature Reference page and the Conformance page.
// `npm run check:examples` verifies every `source`/`expected` here still
// matches the on-disk fixtures, so the docs cannot silently drift.

export const GROUPS = [
  {
    id: "text",
    title: "Text & paragraphs",
    blurb: "The everyday building blocks: paragraphs, headings, rules, comments, links, and images.",
  },
  {
    id: "inline",
    title: "Inline formatting",
    blurb: "Emphasis and inline decorations that work inside any parsed text — paragraphs, list items, table cells, and admonitions.",
  },
  {
    id: "lists",
    title: "Lists",
    blurb: "Ordered, unordered, task, and definition lists.",
  },
  {
    id: "quotes",
    title: "Quotes & callouts",
    blurb: "Set passages apart with quotes, verse, and admonitions.",
  },
  {
    id: "code",
    title: "Code & literal",
    blurb: "Preformatted text that must survive untouched, plus source annotations.",
  },
  {
    id: "containers",
    title: "Containers",
    blurb:
      "Delimited regions that group content: examples, sidebars, tabs, accordions, steps, and id blocks.",
  },
  {
    id: "tables",
    title: "Tables",
    blurb: "Tabular data with an optional title.",
  },
  {
    id: "media",
    title: "Media",
    blurb: "Block images and embedded video.",
  },
  {
    id: "document",
    title: "Document structure & macros",
    blurb:
      "Attributes, titles, object macros, and source-backed references that compose a whole document.",
  },
];

export const FEATURES = [
  // ---- Text & inline -------------------------------------------------------
  {
    slug: "paragraphs",
    title: "Paragraphs",
    group: "text",
    desc: "Consecutive lines form one paragraph; a blank line starts a new one.",
    source: `I really like using Markdown.
It keeps plain text readable.

This is a second paragraph.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>I really like using Markdown.
It keeps plain text readable.</p><p>This is a second paragraph.</p></div>`,
  },
  {
    slug: "heading",
    title: "Headings",
    group: "text",
    desc: "Hash marks set heading levels, mirroring Markdown.",
    source: `# Heading level 1

## Heading level 2`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><h1>Heading level 1</h1><h2>Heading level 2</h2></div>`,
  },
  {
    slug: "inline-formatting",
    title: "Inline formatting",
    group: "inline",
    desc: "Strong, emphasis, inline code, underline, and strikethrough.",
    source: `Use **strong text**, __emphasis__, \`code\`, ___underlined___, and ~~deleted~~ content.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Use <strong>strong text</strong>, <em>emphasis</em>, <span class="monospace">code</span>, <u>underlined</u>, and <del>deleted</del> content.</p></div>`,
  },
  {
    slug: "smart-quotes",
    title: "Smart quotes",
    group: "inline",
    desc: "Wrapping text in a double-quote and backtick pair becomes typographic curly quotes.",
    source: `She said "\`hello there\`" to me.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>She said &ldquo;hello there&rdquo; to me.</p></div>`,
  },
  {
    slug: "highlight",
    title: "Highlight",
    group: "inline",
    desc: "#text# marks (highlights) inline text.",
    source: `This is #very important# text.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>This is<mark class="highlight">very important</mark> text.</p></div>`,
  },
  {
    slug: "subscript",
    title: "Subscript",
    group: "inline",
    desc: "~text~ renders subscript.",
    source: `Water is H~2~O.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Water is H<sub>2</sub>O.</p></div>`,
  },
  {
    slug: "superscript",
    title: "Superscript",
    group: "inline",
    desc: "^text^ renders superscript.",
    source: `Energy is E = mc^2^.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Energy is E = mc<sup>2</sup>.</p></div>`,
  },
  {
    slug: "keyboard",
    title: "Keyboard keys",
    group: "inline",
    desc: "kbd:[keys] renders a keyboard key hint.",
    source: `Press kbd:[Ctrl+C] to copy.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Press <kbd>Ctrl+C</kbd> to copy.</p></div>`,
  },
  {
    slug: "tooltip",
    title: "Tooltip",
    group: "inline",
    desc: "{label}(tooltip text) shows an inline tooltip on hover.",
    source: `The {API}(Application Programming Interface) is stable.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>The <span class="tooltip" title="Application Programming Interface">API</span> is stable.</p></div>`,
  },
  {
    slug: "footnote",
    title: "Footnote",
    group: "inline",
    desc: "footnote:[text] adds a superscript footnote marker.",
    source: `A bold claim.footnote:[A cited source]`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>A bold claim.<sup class="footnote">A cited source</sup></p></div>`,
  },
  {
    slug: "cross-reference",
    title: "Cross-reference",
    group: "inline",
    desc: "<<id,Label>> links to a section anchor elsewhere in the document.",
    source: `See <<installation,Installation>> for setup.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>See <a href="#installation">Installation</a> for setup.</p></div>`,
  },
  {
    slug: "passthrough",
    title: "Passthrough",
    group: "inline",
    desc: "pass:[text] emits its contents without further inline parsing.",
    source: `Keep pass:[**stars**] literal.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Keep **stars** literal.</p></div>`,
  },
  {
    slug: "curly-bang-passthrough",
    title: "Curly-bang passthrough",
    group: "inline",
    desc: "{!text!} strips the passthrough wrapper before inline parsing.",
    source: `Keep {!**stars**!} literal.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Keep <strong>stars</strong> literal.</p></div>`,
  },
  {
    slug: "inline-advanced",
    title: "Everything inline (combined)",
    group: "inline",
    desc: "A single line exercising many inline features at once. Each is also documented on its own below.",
    source: `"\`quote\`" #mark# kbd:[Ctrl+C] {term}(Tip) footnote:[Note] <<my_section,Read>> ~sub~ ^sup^ (c) :smile: pass:[**raw**]`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>&ldquo;quote&rdquo;<mark class="highlight">mark</mark> <kbd>Ctrl+C</kbd> <span class="tooltip" title="Tip">term</span> <sup class="footnote">Note</sup> <a href="#my-section">Read</a> <sub>sub</sub> <sup>sup</sup> © 😄 **raw**</p></div>`,
  },
  {
    slug: "links-and-images",
    title: "Links & inline images",
    group: "text",
    desc: "Markdown-style links and inline images with optional title.",
    source: `Visit [Remarkd](https://example.com) and view ![Logo](assets/logo.png "Remarkd Logo").`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Visit <a href="https://example.com">Remarkd</a> and view <img src="assets/logo.png" alt="Logo" title="Remarkd Logo"/>.</p></div>`,
  },
  {
    slug: "autolink",
    title: "Autolink",
    group: "text",
    desc: "A bare URL followed by [Label] becomes a link.",
    source: `Visit https://example.com/docs[Docs] now.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Visit <a href="https://example.com/docs">Docs</a> now.</p></div>`,
  },
  {
    slug: "horizontal-rule",
    title: "Horizontal rule",
    group: "text",
    desc: "Three dashes on their own line draw a thematic break.",
    source: `Before

---

After`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Before</p><p><hr /></p><p>After</p></div>`,
  },
  {
    slug: "comments",
    title: "Comments",
    group: "text",
    desc: "Text between //// fences is a private writer note and never rendered.",
    source: `Before

////
Hidden
////

After`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Before</p><p>After</p></div>`,
  },
  {
    slug: "page-break",
    title: "Page break",
    group: "text",
    desc: "<<< inserts a print page break marker.",
    source: `Before

<<<

After`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Before</p><p><div style="break-after:page"></div></p><p>After</p></div>`,
  },
  {
    slug: "hardbreaks",
    title: "Hard line breaks",
    group: "text",
    desc: "[%hardbreaks] preserves line breaks inside the following paragraph.",
    source: `[%hardbreaks]
Line one
Line two`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Line one
<br />
Line two</p></div>`,
  },
  {
    slug: "line-continuation",
    title: "Line continuation",
    group: "text",
    desc: "A trailing backslash joins the next source line into the current paragraph.",
    source: `Line one \\
Line two`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Line one
Line two</p></div>`,
  },
  {
    slug: "emoji-aliases",
    title: "Emoji aliases",
    group: "inline",
    desc: "Colon-wrapped names expand to emoji; unknown names are left untouched.",
    source: `Emoji :thumbsup: :thumbsdown: :heart-eyes: :stuck_out_tongue_winking_eye: :unknown:`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Emoji 👍 👎 😍 😜 :unknown:</p></div>`,
  },
  {
    slug: "typographic-symbols",
    title: "Typographic symbols",
    group: "inline",
    desc: "Shorthand for copyright, registered, trademark, section, plus-minus, and bullet.",
    source: `Copyright (c) (C), registered (r) (R), trademark (tm) (TM), section (p) (P), plus-minus (+-), bullet -.-`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Copyright © ©, registered ® ®, trademark ™ ™, section § §, plus-minus ±, bullet &#8226;</p></div>`,
  },

  // ---- Lists ---------------------------------------------------------------
  {
    slug: "unordered-list",
    title: "Unordered list",
    group: "lists",
    desc: "Dash-prefixed lines become bullet items.",
    source: `- First item
- Second item
- Third item`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><ul><li><p>First item</p></li>
<li><p>Second item</p></li>
<li><p>Third item</p></li></ul></div>`,
  },
  {
    slug: "ordered-list",
    title: "Ordered list",
    group: "lists",
    desc: "Number-prefixed lines become a numbered list.",
    source: `1. First item
2. Second item
3. Third item`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><ol><li><p>First item</p>
<ol><li><p>Second item</p>
<ol><li><p>Third item</p></li></ol></li></ol></li></ol></div>`,
  },
  {
    slug: "checkboxes",
    title: "Task list",
    group: "lists",
    desc: "Bracketed markers render checkboxes; _ and * make them editable.",
    source: `- [ ] Todo
- [x] Done
- [_] Editable
- [*] Editable done`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><ul><li><p><input type="checkbox" readonly="readonly"> Todo</p></li>
<li><p><input type="checkbox" readonly="readonly" checked> Done</p></li>
<li><p><input type="checkbox"> Editable</p></li>
<li><p><input type="checkbox" checked> Editable done</p></li></ul></div>`,
  },
  {
    slug: "definition-list",
    title: "Definition list",
    group: "lists",
    desc: "term:: definition pairs render as a description list.",
    source: `CPU:: Central processor
GPU:: Graphics processor`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><dl><dt>CPU</dt><dd>Central processor</dd><dt>GPU</dt><dd>Graphics processor</dd></dl></div>`,
  },

  // ---- Quotes & callouts ---------------------------------------------------
  {
    slug: "blockquotes",
    title: "Block quote",
    group: "quotes",
    desc: "Text inside ____ fences becomes a blockquote.",
    source: `____
Dorothy followed her through many of the beautiful rooms.
Everything is going according to **plan**.
____`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><blockquote><p>Dorothy followed her through many of the beautiful rooms. Everything is going according to **plan**.</p></blockquote></div>`,
  },
  {
    slug: "verse",
    title: "Verse",
    group: "quotes",
    desc: "A [verse] block preserves line breaks and leading spaces.",
    source: `[verse]
____
Line one
  Line two
____`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><pre class="verse-block">Line one
  Line two</pre></div>`,
  },
  {
    slug: "admonition",
    title: "Admonition",
    group: "quotes",
    desc: "A leading LABEL: turns the paragraph into a styled note.",
    source: `NOTE: Remember this
Still important`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="hint-note"><strong class="hint-caption">NOTE: </strong> Remember this
Still important</div></div>`,
  },

  // ---- Code & literal ------------------------------------------------------
  {
    slug: "code-fence",
    title: "Code fence",
    group: "code",
    desc: "Triple-backtick fences keep code verbatim, indentation included.",
    source: "```\ncode line 1\n    code line 2\ncode line 3\n```",
    expected: `<div class="remarkd-section section--level0 section--with-content"><code>code line 1
    code line 2
code line 3</code></div>`,
  },
  {
    slug: "literal-block",
    title: "Literal block",
    group: "code",
    desc: "A .... block renders text literally, escaping HTML.",
    source: `....
literal <text>
....`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><code>literal &lt;text&gt;</code></div>`,
  },
  {
    slug: "listing-block",
    title: "Listing block",
    group: "code",
    desc: "A ---- block is a listing with escaped contents.",
    source: `----
<raw>
----`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="listing-block"><div class="content">&lt;raw&gt;</div></div></div>`,
  },
  {
    slug: "generic-container",
    title: "Generic container",
    group: "code",
    desc: "!!!! fences preserve their contents as a verbatim code block.",
    source: `!!!!
Inside **content**
!!!!`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><code>Inside **content**</code></div>`,
  },
  {
    slug: "callout-block",
    title: "Callout annotation",
    group: "code",
    desc: "A <n> marker annotates a line of a listing.",
    source: `<1> Explain the first line`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><li class="callout" data-marker="1">Explain the first line</li></div>`,
  },

  // ---- Containers ----------------------------------------------------------
  {
    slug: "example-block",
    title: "Example block",
    group: "containers",
    desc: "==== fences wrap parsed content in an example container.",
    source: `====
Example **content**
====`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="example-block"><div class="content"><p>Example <strong>content</strong></p></div></div></div>`,
  },
  {
    slug: "sidebar-block",
    title: "Sidebar block",
    group: "containers",
    desc: "**** fences wrap parsed content in a sidebar container.",
    source: `****
Sidebar text
****`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="sidebar-block"><div class="content"><p>Sidebar text</p></div></div></div>`,
  },
  {
    slug: "accordion",
    title: "Accordion",
    group: "containers",
    desc: "_-_# panels collapse behind clickable headers.",
    source: `_-_#one [name=One]
First panel
_-_#two [name=Two]
Second panel`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="accordion-container"><button class="accordion">One</button><div class="panel"><p>First panel</p></div>
<button class="accordion">Two</button><div class="panel"><p>Second panel</p></div></div></div>`,
  },
  {
    slug: "tabs",
    title: "Tabs",
    group: "containers",
    desc: "_|_# panels render as a tab group; the first tab is active.",
    source: `_|_#first [name=First]
First content
_|_#second [name=Second]
Second content`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="tab-group"><ul class="tab-header"><li><a href="#" data-tab-focus-key="first" class="active">First</a></li><li><a href="#" data-tab-focus-key="second">Second</a></li></ul><div class="tabs"><div class="tab" data-tab-key="first"><div class="content"><p>First content</p></div></div>
<div class="tab" data-tab-key="second"><div class="content"><p>Second content</p></div></div></div></div></div>`,
  },
  {
    slug: "steps",
    title: "Steps",
    group: "containers",
    desc: "_|- items render as numbered steps with an optional image.",
    source: `_|- First step [img=step.png]
Do first
_|- Second step
Do second`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="steps-container"><div class="content"><div class="step"><div class="step-content"><h3>First step </h3><p>Do first</p></div><div class="step-image"><img src="step.png" alt="" /></div></div>
<div class="step"><div class="step-content"><h3>Second step</h3><p>Do second</p></div></div></div></div></div>`,
  },
  {
    slug: "id-block",
    title: "Id block",
    group: "containers",
    desc: "!!name!! opens a div with that id for anchoring.",
    source: `!!intro!!
Content
!!!!`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div id="intro"><p>Content
!!!!</p></div></div>`,
  },

  // ---- Tables --------------------------------------------------------------
  {
    slug: "table",
    title: "Table",
    group: "tables",
    desc: "|=== fences delimit a table; the first row is the header. A leading .Title names it.",
    source: `.Data
|===
|Name |Value

|Alpha |1
|===`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="table-block"><div class="title">Data</div><table class="remarkd-table"><thead><tr><th>Name</th><th>Value</th></tr></thead><tbody><tr><td>Alpha</td><td>1</td></tr></tbody></table></div></div>`,
  },

  // ---- Media ---------------------------------------------------------------
  {
    slug: "asciidoc-image",
    title: "Block image",
    group: "media",
    desc: "image::src[Alt,width,height] places a block-level image.",
    source: `image::diagram.png[Diagram,640,480]`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p><img class="block" src="diagram.png" alt="Diagram" width="640" height="480"/></p></div>`,
  },
  {
    slug: "video",
    title: "Video",
    group: "media",
    desc: "The video macro embeds YouTube (or self-hosted) players with aspect and timing options.",
    source: `{{video:dQw4w9WgXcQ source=youtube aspect=4:3 start=10 end=20}}`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="video-container" style="padding-top: 75%"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?start=10;end=20;rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>`,
  },

  // ---- Document structure & macros -----------------------------------------
  {
    slug: "asciidoc-section",
    title: "AsciiDoc sections",
    group: "document",
    desc: "== headings create nested Remarkd section blocks with generated ids.",
    source: `== Section One

Body

=== Child

Nested`,
    expected: `<div class="remarkd-section section--level1 section--with-content" id="section-one"><h2>Section One</h2><p>Body</p><div class="remarkd-section section--level2 section--with-content" id="child"><h3>Child</h3><p>Nested</p></div></div>`,
  },
  {
    slug: "attributes-title",
    title: "Block title & roles",
    group: "document",
    desc: "A .Title line names the next block; [.role] adds a class.",
    source: `.Intro
[.lead]
Intro paragraph`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p class="lead"><div class="title">Intro</div>Intro paragraph</p></div>`,
  },
  {
    slug: "document-attributes",
    title: "Document attributes",
    group: "document",
    desc: ":name: value defines an attribute; {name} substitutes it.",
    source: `:product: Remarkd

This is {product}.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>This is Remarkd.</p></div>`,
  },
  {
    slug: "conditionals",
    title: "Conditionals",
    group: "document",
    desc: "ifdef and ifndef include or drop content based on document attributes.",
    source: `:flag:

ifdef::flag[]
Shown
endif::[]

ifndef::flag[]
Hidden
endif::[]

ifdef::missing[Inline hidden]
ifndef::missing[Inline shown]`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Shown</p><p>Inline shown</p></div>`,
  },
  {
    slug: "object-macros",
    title: "Object macros",
    group: "document",
    desc: "{{...}} macros emit links, breaks, anchors, meters, and more.",
    source: `{{link:https://example.com text=Example target=_blank}} {{br}} {{anchor name=top}} {{meter id=progress min=1 max=10 value=5 text=Half label=Progress}}`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><a href="https://example.com" target="_blank">Example</a> <br> <a name="top"></a> <label for="progress" class="remarkd-meter-label">Progress</label><meter id="progress" class="remarkd-meter"  min="1" max="10" value="5"">Half</meter></div>`,
  },
  {
    slug: "object-attributes",
    title: "Object macro attributes",
    group: "document",
    desc: "Macros accept positional and named attributes, roles, and styling hints.",
    source: `{{link:https://example.com text=Example target=_blank hreflang=en}} {{img src=https://example.com/logo.png alt=Logo .hero float=right}} {{video:/media/demo.mp4 source=self type=video/webm aspect=1:1}}`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><a href="https://example.com" target="_blank" hreflang="en">Example</a> <img src="https://example.com/logo.png" alt="Logo" style="display: inline-block;max-width: 100%;float: right;" class="hero" /> <div class="video-container" style="padding-top: 100%"><video controls><source src="/media/demo.mp4" type="video/webm"></video></div></div>`,
  },
  {
    slug: "reference-list",
    title: "References",
    group: "document",
    desc: "{{ref}} marks a source-backed fact; {{reflist}} prints the numbered list.",
    source: `Fact{{ref content=Source code=src1}}

# References

{{reflist}}`,
    expected: `<div class="remarkd-section section--level0 section--with-content">Fact<sup class="reference"><a id="rmdref-bdy-src1" href="#rmdref-ft-src1">[1]</a></sup><h1>References</h1><ol class="reference"><li id="rmdref-ft-src1"><a href="#rmdref-bdy-src1" class="reference-tobody">^</a> Source</li></ol></div>`,
  },
  {
    slug: "include",
    title: "Includes",
    group: "document",
    desc: "include::file[] renders another Remarkd file in place.",
    source: `include::requirements/features/include/content.remarkd[]

After the include.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><div class="remarkd-section section--level0 section--with-content"><p>Included <strong>content</strong>
Second line</p></div><p>After the include.</p></div>`,
  },
  {
    slug: "partial",
    title: "Partials",
    group: "document",
    desc: "t::partial::file injects another Remarkd file before parsing.",
    source: `t::partial::requirements/features/partial/content.remarkd

After the partial.`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>Partial <strong>content</strong>
Second line</p><p>After the partial.</p></div>`,
  },
  {
    slug: "conditionals-advanced",
    title: "Conditionals",
    group: "document",
    desc: "ifdef/ifndef/iftrue/iffalse/ifempty/ifnempty and ifeval include or drop content based on attributes; combine conditions with + (and) and , (or), and close block forms with endif::[].",
    source: `:truth:
:lie!:
:left:

ifdef::truth+left[]
AND shown
endif::[]

ifdef::missing,truth[]
OR shown
endif::[]

iftrue::truth[True inline]

iffalse::lie[False inline]

ifempty::missing[Empty missing inline]

ifnempty::truth[Nonempty inline]

ifeval::[5 > 3]
Eval greater shown
endif::[]

ifeval::[a in a,b]
Eval in shown
endif::[]

ifdef::missing[]
Hidden parent
ifdef::truth[Hidden nested inline]
endif::[]
endif::[]`,
    expected: `<div class="remarkd-section section--level0 section--with-content"><p>AND shown</p><p>OR shown</p><p>True inline</p><p>False inline</p><p>Empty missing inline</p><p>Nonempty inline</p><p>Eval greater shown</p><p>Eval in shown</p></div>`,
  },
  {
    slug: "object-fallbacks",
    title: "Object macro fallbacks",
    group: "document",
    desc: "How object macros handle missing or edge-case input: an anchor without a name, an empty reference list, and self-hosted vs YouTube video with different aspect ratios.",
    source: `{{anchor}} {{reflist}} {{video:clip.mp4 source=self}} {{video:abc123 aspect=3:2}} {{video:def456 aspect=8:5}}`,
    expected: `<div class="remarkd-section section--level0 section--with-content">[ANCHOR MISSING NAME]  <div class="video-container" style="padding-top: 56.25%"><video controls><source src="clip.mp4" type="video/mp4"></video></div> <div class="video-container" style="padding-top: 66.66%"><iframe src="https://www.youtube.com/embed/abc123?rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div> <div class="video-container" style="padding-top: 62.5%"><iframe src="https://www.youtube.com/embed/def456?rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>`,
  },
];

export function featuresByGroup() {
  return GROUPS.map((group) => ({
    ...group,
    features: FEATURES.filter((feature) => feature.group === group.id),
  }));
}
