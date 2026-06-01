// reference.js — builds the grouped Feature Reference page from the shared
// feature catalogue. Each feature shows its Remarkd source, a live rendered
// preview, and a toggle that reveals the exact HTML the parser produced.
//
// This runs before site.js in the page, so the group <h2 id> headings exist
// when site.js builds the "on this page" table of contents.

import { featuresByGroup } from "./features.js";
import { parse, makePreview } from "./preview.js";

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

function buildExample(feature) {
  const html = parse(feature.source);

  const source = el(
    "div",
    { class: "fe-pane fe-source" },
    el("span", { class: "fe-label" }, "Remarkd"),
    el("pre", {}, el("code", {}, feature.source)),
  );

  const preview = el(
    "div",
    { class: "fe-pane fe-preview" },
    el("span", { class: "fe-label" }, "Rendered"),
    makePreview(html),
  );

  const htmlPane = el(
    "div",
    { class: "fe-html", hidden: "hidden" },
    el("pre", {}, el("code", {}, html)),
  );

  const toggle = el("button", { type: "button", class: "fe-toggle" }, "Show HTML");
  toggle.addEventListener("click", () => {
    const showing = htmlPane.hasAttribute("hidden");
    htmlPane.toggleAttribute("hidden", !showing);
    toggle.textContent = showing ? "Hide HTML" : "Show HTML";
  });

  return el(
    "div",
    { class: "feature-example" },
    el("div", { class: "fe-grid" }, source, preview),
    el("div", { class: "fe-actions" }, toggle),
    htmlPane,
  );
}

function buildFeature(feature) {
  return el(
    "article",
    { class: "feature-entry", id: feature.slug },
    el("h3", {}, feature.title),
    el("p", { class: "feature-desc" }, feature.desc),
    buildExample(feature),
  );
}

function buildGroup(group) {
  const section = el(
    "section",
    { class: "feature-group", "aria-labelledby": `${group.id}-h` },
    el("h2", { id: group.id }, group.title),
    el("p", { class: "group-blurb" }, group.blurb),
  );
  group.features.forEach((feature) => section.append(buildFeature(feature)));
  return section;
}

function init() {
  const root = document.querySelector("#reference-root");
  if (!root) return;
  for (const group of featuresByGroup()) {
    root.append(buildGroup(group));
  }
}

init();
