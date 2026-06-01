import { Remarkd } from "./assets/remarkd.js";
import { enhance } from "./assets/preview.js";

const editor = document.querySelector("#playground-input");
const preview = document.querySelector("#playground-preview");
const htmlOutput = document.querySelector("#playground-html");
const sampleButtons = document.querySelectorAll("[data-sample]");
const tabButtons = document.querySelectorAll(".pg-tabs [data-pane]");
const panes = document.querySelectorAll(".pg-output .pg-pane");

const samples = {
  basics: `# Product Guide

This is a paragraph with **strong text**, __emphasis__, and a [link](https://example.com).

- [ ] Draft the guide
- [x] Review the output

NOTE: This is a note for readers.`,
  guide: `.Welcome
[.lead]
Remarkd keeps the source readable.

NOTE: Use notes for reader-critical details.

CPU:: Central processor
GPU:: Graphics processor`,
  table: `.Release Matrix
|===
|Version |Status

|1.0 |Stable

|2.0 |Preview
|===`,
  tabs: `_|_#install [name=Install]
Run the installer.
_|_#configure [name=Configure]
Set the required values.`,
  steps: `_|- Install the package [img=step.png]
Add Remarkd to your project.
_|- Call the parser
Pass your text to parse().`,
  references: `Fact{{ref content=Source code=src1}}

# References

{{reflist}}`,
  document: `= Release Notes
:product: Remarkd
:version: 2.0

# {product} {version}

The document header above is processed and hidden — but its attributes still
fill in: this release is **{product} {version}**.`,
};

function render() {
  // Parse in document-header mode: a leading "= Title", author/revision, and
  // :attribute: lines are read as a header (hidden from the output) while their
  // values remain available for {substitution} in the body. (Leading "// "
  // comments before the title are handled by the parsers themselves.)
  const html = Remarkd.parse(editor.value, true);
  // Render inline (not in an iframe) so tabs and accordions are interactive,
  // exactly like the reference previews.
  preview.innerHTML = html;
  enhance(preview);
  htmlOutput.textContent = html;
}

function showPane(name) {
  panes.forEach((pane) => {
    pane.toggleAttribute("hidden", pane.dataset.pane !== name);
  });
  tabButtons.forEach((button) => {
    const active = button.dataset.pane === name;
    button.classList.toggle("active", active);
    button.setAttribute("aria-selected", active ? "true" : "false");
  });
}

tabButtons.forEach((button) => {
  button.addEventListener("click", () => showPane(button.dataset.pane));
});

sampleButtons.forEach((button) => {
  button.addEventListener("click", () => {
    editor.value = samples[button.dataset.sample] ?? samples.basics;
    render();
  });
});

editor.addEventListener("input", render);
editor.value = samples.basics;
showPane("preview");
render();
