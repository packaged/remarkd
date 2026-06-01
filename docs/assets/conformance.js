// conformance.js — builds the cross-language conformance table.
//
// The TypeScript column is computed live in your browser (the bundled parser is
// run on each fixture and compared to expected.html). PHP and Go cannot run in
// the browser, so their results are read from docs/assets/conformance-status.json,
// produced locally by `npm run build:conformance`. If that file is missing, the
// PHP/Go columns show "unknown" and the page explains how to generate it.

import { FEATURES } from "./features.js";
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

// value: true (pass), false (fail), or null (unknown).
function statusCell(value, lang) {
  if (value === null || value === undefined) {
    return el("td", { class: "status-cell" }, el("span", { class: "status-unknown", title: `${lang} result not built` }, "?"));
  }
  const span = el(
    "span",
    {
      class: value ? "status-pass" : "status-fail",
      title: value ? `${lang} matches expected output` : `${lang} mismatch`,
    },
    value ? "✓" : "✗",
  );
  return el("td", { class: "status-cell" }, span);
}

async function loadStatus() {
  try {
    const response = await fetch("assets/conformance-status.json", { cache: "no-cache" });
    if (!response.ok) return null;
    return await response.json();
  } catch {
    return null;
  }
}

function buildRow(feature, status) {
  const tsLive = parse(feature.source) === feature.expected;
  const entry = status?.features?.[feature.slug];
  const php = status ? entry?.php ?? false : null;
  const go = status ? entry?.go ?? false : null;

  return el(
    "tr",
    {},
    el("th", { scope: "row" }, el("a", { href: `reference.html#${feature.slug}` }, feature.title)),
    el("td", {}, makePreview(feature.expected)),
    statusCell(php, "PHP"),
    statusCell(go, "Go"),
    statusCell(tsLive, "TypeScript"),
    el("td", {}, el("pre", {}, el("code", {}, feature.source))),
    el("td", {}, el("pre", {}, el("code", {}, feature.expected))),
  );
}

function tally(status, lang) {
  if (!status) return null;
  return Object.values(status.features).filter((f) => f[lang] === true).length;
}

async function init() {
  const root = document.querySelector("#conformance-root");
  if (!root) return;

  const status = await loadStatus();
  const total = FEATURES.length;

  const tbody = el("tbody");
  let tsPasses = 0;
  for (const feature of FEATURES) {
    if (parse(feature.source) === feature.expected) tsPasses += 1;
    tbody.append(buildRow(feature, status));
  }

  const summary = document.querySelector("#conformance-summary");
  if (summary) {
    if (status) {
      const when = status.generatedAt ? status.generatedAt.slice(0, 10) : "unknown date";
      const phpText = status.available.php ? `${tally(status, "php")}/${total}` : "not built";
      const goText = status.available.go ? `${tally(status, "go")}/${total}` : "not built";
      summary.textContent =
        `TypeScript (live in your browser): ${tsPasses}/${total} match. ` +
        `PHP: ${phpText} · Go: ${goText} — from the build step (generated ${when}).`;
    } else {
      summary.textContent =
        `TypeScript (live in your browser): ${tsPasses}/${total} match. ` +
        `PHP and Go results not found — run "npm run build:conformance" to generate them.`;
    }
  }

  const table = el(
    "table",
    { class: "comparison-table hide-extra" },
    el(
      "thead",
      {},
      el(
        "tr",
        {},
        el("th", {}, "Feature"),
        el("th", {}, "Preview"),
        el("th", {}, "PHP"),
        el("th", {}, "Go"),
        el("th", {}, "TypeScript"),
        el("th", {}, "Remarkd source"),
        el("th", {}, "Expected HTML"),
      ),
    ),
    tbody,
  );

  const toggle = el("button", { type: "button", class: "col-toggle" }, "Show source & HTML");
  toggle.addEventListener("click", () => {
    const hidden = table.classList.toggle("hide-extra");
    toggle.textContent = hidden ? "Show source & HTML" : "Hide source & HTML";
  });

  root.append(
    el("div", { class: "table-controls" }, toggle),
    el("div", { class: "table-scroll" }, table),
  );
}

init();
