// preview.js — shared helpers for rendering Remarkd inline and making the
// rendered output (tabs, accordions) interactive. Used by reference.js and
// conformance.js.

import { Remarkd } from "./remarkd.js";

const PARTIALS = {
  "requirements/features/include/content.remarkd": `Included **content**
Second line`,
  "requirements/features/partial/content.remarkd": `Partial **content**
Second line`,
};

export function parse(source) {
  return Remarkd.parse(source, false, { partials: PARTIALS });
}

export function escapeHtml(value) {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

// Build a styled, interactive preview node for a chunk of parsed Remarkd HTML.
export function makePreview(html) {
  const frame = document.createElement("div");
  frame.className = "remarkd-rendered";
  frame.innerHTML = html;
  enhance(frame);
  return frame;
}

// Wire up the interactive behaviours that the parser markup implies but that
// require JS: tab switching and accordion toggling.
export function enhance(root) {
  root.querySelectorAll(".tab-group").forEach(setupTabs);
  root.querySelectorAll(".accordion-container").forEach(setupAccordion);
}

function setupTabs(group) {
  const links = [...group.querySelectorAll(".tab-header a[data-tab-focus-key]")];
  const panels = [...group.querySelectorAll(".tab[data-tab-key]")];
  const show = (key) => {
    links.forEach((l) =>
      l.classList.toggle("active", l.dataset.tabFocusKey === key),
    );
    panels.forEach((p) => {
      p.hidden = p.dataset.tabKey !== key;
    });
  };
  links.forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      show(link.dataset.tabFocusKey);
    });
  });
  if (links.length) show(links[0].dataset.tabFocusKey);
}

function setupAccordion(container) {
  const buttons = [...container.querySelectorAll("button.accordion")];
  buttons.forEach((button, index) => {
    const panel = button.nextElementSibling;
    if (!panel || !panel.classList.contains("panel")) return;
    const open = index === 0;
    panel.hidden = !open;
    button.classList.toggle("is-open", open);
    button.addEventListener("click", () => {
      const nowOpen = panel.hidden;
      panel.hidden = !nowOpen;
      button.classList.toggle("is-open", nowOpen);
    });
  });
}
