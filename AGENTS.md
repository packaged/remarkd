# Remarkd Format Notes For Agents

Remarkd is Markdown plus selected AsciiDoc-style syntax. Use this as a compact
reference when reading or writing `.remarkd` content.

## Document Basics

- Blank lines separate paragraphs.
- `\` at the end of a line continues the paragraph on the next source line.
- `+` on its own line, or a line ending with ` +`, creates a hard line break.
- `// comment` lines are ignored.
- `{!text!}` outputs `text` while bypassing special handling of the wrapper.
- Document attributes are `:name: value`; references use `{name}`.
- Attributes before a block use `[ ... ]`, for example `[.lead]`, `[#intro]`,
  `[verse]`, or `[%hardbreaks]`.
- A block title is `.Title` on the line before the block.

## Headings And Sections

- Markdown headings: `# H1` through `###### H6`.
- Section headings: `== Section` through `====== Section`.
- Section ids are generated from text unless preceded by `[#custom-id]`.
- Cross reference: `<<section-id,optional label>>`.

## Blocks

- Unordered lists: `* item` or `- item`.
- Ordered lists: `. item`, `1. item`, or nested dotted markers.
- Checkboxes: `[ ]`, `[x]`, `[_]`, `[*]`.
- Definition lists: `Term:: Definition`.
- Table:

```remarkd
.Optional title
|===
| Name | Value

| Alpha | 1
|===
```

- Code/verbatim: fence with three backticks, `....`, or `!!!!`.
- Listing block: `----` around raw listing text.
- Example block: `====` around nested Remarkd content.
- Sidebar block: `****` around nested Remarkd content.
- Quote block: `____` around quoted text.
- Verse block: `[verse]` then `____` around preformatted verse.
- Page break: `<<<`.
- Horizontal rule: `---`.
- ID block: `!!id!!` before content that should be wrapped with that id.

## Admonitions And Callouts

- Admonitions: `NOTE: text`, `TIP: text`, `WARNING: text`, `CAUTION: text`,
  `IMPORTANT: text`, `NOTICE: text`, `DANGER: text`, or `SUCCESS: text`.
- Use `NOTE| text` form to omit the visible caption.
- Callout list item: `<1> Explanation`.
- Inline callout marker: `<1>`.

## Tabs, Accordions, Steps

```remarkd
_|_#first name="First"
First tab content
_|_#second name="Second"
Second tab content

_-_#one name="One"
First panel content
_-_#two name="Two"
Second panel content

_|- First step [img=step.png]
Step content
_|- Second step
More content
```

## Inline Formatting

- Strong: `**text**` or `*text*`.
- Emphasis: `__text__` or `_text_`.
- Code: `` `text` `` or ````text````.
- Underline: `___text___`.
- Deleted: `~~text~~`.
- Highlight: `#text#`.
- Subscript: `~text~`.
- Superscript: `^text^`.
- Keyboard key: `kbd:[Ctrl+C]`.
- Tooltip: `{label}(tooltip text)`.
- Footnote: `footnote:[text]`.
- Passthrough raw text: `pass:[raw]` or `+++raw+++`.
- Smart quotes: ``"`quoted text`"``.
- Typographic shortcuts: `(c)`, `(r)`, `(tm)`, `(p)`, `(+-)`, `-.-`.
- Emoji aliases: `:smile:`, `:thumbsup:`, etc.
- Inline style span: `[class]##text##`.

## Links And Media

- Link: `[label](https://example.com)`.
- Autolink: `https://example.com[optional label]`.
- Image: `![alt](src "optional title")`.
- AsciiDoc image: `image::src[alt,width,height]`.

## Object Macros

```remarkd
{{link:https://example.com text=Example target=_blank hreflang=en}}
{{img src=/logo.png alt=Logo .hero float=right}}
{{video:dQw4w9WgXcQ start=10 end=20 aspect=4:3}}
{{video:/media/demo.mp4 source=self type=video/mp4 aspect=16:9}}
{{anchor name=top}}
{{br}}
{{meter id=progress min=1 max=10 value=5 text=Half label=Progress}}
{{ref code=src1 content="Source text"}}
{{reflist}}
```

Macro attributes can be positional, class-like (`.hero`), id-like (`#intro`),
or named (`key=value`). Quote values containing spaces.

## Conditionals

```remarkd
ifdef::name[Inline content if set]
ifndef::name[]
Hidden unless name is missing
endif::[]

iftrue::flag[Shown when flag is true]
iffalse::flag[Shown when flag is false]
ifempty::name[Shown when missing or empty]
ifnempty::name[Shown when non-empty]
ifeval::[][1 < 2]
Shown when expression is true
endif::[]
```
