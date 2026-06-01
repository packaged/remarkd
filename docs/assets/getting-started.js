// getting-started.js — fills the writer guide's example placeholders with a
// "You write" source pane and a live "You get" rendered preview. Sources live
// here so the HTML stays clean and whitespace stays exact.

import { parse, makePreview } from "./preview.js";

const EXAMPLES = {
  firstdoc: `# Meeting Notes

We shipped the **beta** today.

- [x] Deploy the release
- [ ] Announce to users

NOTE: Gather feedback before the next release.`,

  paragraphs: `These two lines
stay in one paragraph.

A blank line starts a new paragraph.`,

  headings: `# Document title
## A section
### A subsection`,

  emphasis: "Use **bold**, __italic__, `code`, and ~~strikethrough~~.",

  lists: `- Apples
- Oranges
- Pears`,

  tasks: `- [x] Written
- [ ] Reviewed`,

  links: `Read the [reference](reference.html) for every feature.`,

  notes: `NOTE: Notes call out important details.

TIP: Tips share helpful advice.

WARNING: Warnings flag risks.`,

  quote: `____
A quote stands apart from the text around it.
____`,

  code: "Show commands in a fenced block:\n\n```\nnpm install\n```",

  table: `.Roadmap
|===
|Phase |Status

|Alpha |Shipped

|Beta |In progress
|===`,

  titled: `.Summary
[.lead]
A titled, emphasised lead paragraph.`,
};

function el(tag, attrs = {}, ...children) {
  const node = document.createElement(tag);
  for (const [key, value] of Object.entries(attrs)) {
    if (key === "class") node.className = value;
    else if (value != null) node.setAttribute(key, value);
  }
  for (const child of children) {
    if (child == null) continue;
    node.append(child instanceof Node ? child : document.createTextNode(child));
  }
  return node;
}

function buildExample(source) {
  return el(
    "div",
    { class: "feature-example" },
    el(
      "div",
      { class: "fe-grid" },
      el(
        "div",
        { class: "fe-pane fe-source" },
        el("span", { class: "fe-label" }, "You write"),
        el("pre", {}, el("code", {}, source)),
      ),
      el(
        "div",
        { class: "fe-pane fe-preview" },
        el("span", { class: "fe-label" }, "You get"),
        makePreview(parse(source)),
      ),
    ),
  );
}

for (const mount of document.querySelectorAll(".guide-mount")) {
  const source = EXAMPLES[mount.dataset.example];
  if (source) mount.replaceWith(buildExample(source));
}
