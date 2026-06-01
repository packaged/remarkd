// site.js — injects the shared chrome (top bar, sidebar nav, "on this page"
// table of contents, and footer) into every documentation page.
//
// A page only needs to provide:
//   <main class="content" data-page="reference"> … </main>
// plus <link rel="stylesheet" href="style.css"> and this module. The nav is
// defined once here, so adding a page never means editing every other file.

const PAGES = [
  { id: "home", href: "index.html", label: "Home" },
  { id: "getting-started", href: "getting-started.html", label: "Getting Started" },
  { id: "concepts", href: "concepts.html", label: "Concepts" },
  { id: "reference", href: "reference.html", label: "Reference" },
  { id: "playground", href: "playground.html", label: "Playground" },
  { id: "conformance", href: "conformance.html", label: "Conformance" },
];

const REPO_URL = "https://github.com/packaged/remarkd";

function el(tag, attrs = {}, ...children) {
  const node = document.createElement(tag);
  for (const [key, value] of Object.entries(attrs)) {
    if (key === "class") node.className = value;
    else if (value !== null && value !== undefined) node.setAttribute(key, value);
  }
  for (const child of children) {
    node.append(child instanceof Node ? child : document.createTextNode(child));
  }
  return node;
}

function buildTopbar(currentId) {
  const nav = el("nav", { class: "topnav", "aria-label": "Primary" });
  for (const page of PAGES) {
    const link = el("a", { href: page.href }, page.label);
    if (page.id === currentId) link.setAttribute("aria-current", "page");
    nav.append(link);
  }
  nav.append(el("a", { href: REPO_URL, rel: "noopener" }, "GitHub"));
  return el(
    "header",
    { class: "topbar" },
    el("a", { class: "site-logo", href: "index.html" }, "Remarkd"),
    nav,
  );
}

function buildTableOfContents(main) {
  const headings = main.querySelectorAll("h2[id]");
  if (headings.length < 2) return null;
  const toc = el("nav", { class: "nav toc", "aria-label": "On this page" });
  for (const heading of headings) {
    toc.append(el("a", { href: `#${heading.id}` }, heading.textContent.trim()));
  }
  return toc;
}

// The sidebar is purely the current page's "on this page" table of contents.
// Primary page navigation lives in the top bar, so the two never duplicate each
// other. Pages without enough headings get no sidebar at all.
function buildSidebar(main) {
  const toc = buildTableOfContents(main);
  if (!toc) return null;
  return el("aside", { class: "sidebar" }, el("h2", {}, "On this page"), toc);
}

function buildFooter() {
  return el(
    "footer",
    { class: "site-footer" },
    el("p", {},
      "Remarkd — a human-first text format. ",
      el("a", { href: REPO_URL, rel: "noopener" }, "Source on GitHub"),
      ".",
    ),
  );
}

function init() {
  const main = document.querySelector("main.content");
  if (!main) return;
  const currentId =
    main.dataset.page ||
    PAGES.find((p) => location.pathname.endsWith(p.href))?.id ||
    "home";

  document.body.prepend(buildTopbar(currentId));

  const sidebar = buildSidebar(main);
  if (sidebar) {
    const layout = el("div", { class: "layout" });
    main.replaceWith(layout);
    layout.append(sidebar, main);
    layout.after(buildFooter());
  } else {
    // No table of contents: let the content use the full width.
    main.classList.add("content--full");
    main.after(buildFooter());
  }

  // Smooth-scroll handled by CSS; mark active TOC entry on scroll.
  highlightOnScroll(main);
}

function highlightOnScroll(main) {
  const tocLinks = [...document.querySelectorAll(".toc a")];
  if (!tocLinks.length) return;
  const byId = new Map(
    tocLinks.map((link) => [link.getAttribute("href").slice(1), link]),
  );
  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        tocLinks.forEach((l) => l.classList.remove("active"));
        byId.get(entry.target.id)?.classList.add("active");
      }
    },
    { rootMargin: "-62px 0px -70% 0px", threshold: 0 },
  );
  main.querySelectorAll("h2[id]").forEach((h) => observer.observe(h));
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
