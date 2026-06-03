# Pinnacle — Modern Business & Agency HTML5 Template

![Version](https://img.shields.io/badge/version-1.0.0-5b3df5)
![License](https://img.shields.io/badge/license-MIT-success)
![Made with](https://img.shields.io/badge/made%20with-HTML5%20%7C%20CSS3%20%7C%20Vanilla%20JS-ff5c8a)

**Pinnacle** is a modern, fully responsive, **mobile-first** one-page HTML5
template for businesses, agencies, startups and freelancers. It is built with
**semantic HTML5, pure CSS3 and vanilla JavaScript** — no heavy frameworks, no
build step, no dependencies. Just open and edit.

---

## ✨ Features

- 📱 **Mobile-first & fully responsive** — looks great from 320px to 4K
- 🎨 **Modern design** — clean layout, gradient accents, smooth micro-interactions
- ⚡ **Zero dependencies** — no jQuery, no Bootstrap, no build tools
- 🍔 **Animated hamburger menu** with off-canvas drawer & overlay
- 🪄 **Smooth scrolling** + scroll-spy active link highlighting
- 🎬 **Scroll-reveal animations** powered by `IntersectionObserver`
- 🔢 **Animated stat counters**
- 🗂️ **Filterable portfolio** gallery
- 💬 **Auto-playing testimonials slider** (pause on hover, dots + arrows)
- ✅ **Client-side form validation** (contact + newsletter)
- ⬆️ **Back-to-top** button
- ♿ **Accessible** — ARIA attributes, keyboard support, `prefers-reduced-motion`
- 🔍 **SEO-ready** — meta tags, Open Graph, semantic structure
- 🧩 **Well-commented, organized code** — easy to customize

---

## 📂 Folder Structure

```
template-monster-1/
├── index.html              # Main page (all sections)
├── README.md               # This file
├── LICENSE                 # MIT license
└── assets/
    ├── css/
    │   └── style.css       # All styles (organized with a table of contents)
    ├── js/
    │   └── main.js         # All scripts (one module per feature)
    └── images/
        ├── favicon.svg     # Site favicon
        └── og-image.svg    # Social-share preview image
```

This structure follows the conventions used by TemplateMonster HTML packages:
a single entry `index.html`, an `assets/` directory split into `css/`, `js/`
and `images/`, and documentation at the root.

---

## 🚀 Getting Started

No installation or build step is required.

### Option 1 — Open directly
Double-click `index.html` to open it in your browser.

### Option 2 — Run a local server (recommended)
Some browser features behave best over HTTP. Pick whichever you have:

```bash
# Python 3
python -m http.server 8000

# Node.js (npx)
npx serve

# PHP
php -S localhost:8000
```

Then visit <http://localhost:8000>.

---

## 🎨 Customization

### Colors & fonts
All design tokens live at the top of `assets/css/style.css` under
**`01. DESIGN TOKENS`**. Change a value once and it updates everywhere:

```css
:root {
  --c-primary: #5b3df5;   /* brand color */
  --c-accent:  #ff5c8a;   /* secondary accent */
  --c-ink:     #14122b;   /* headings */
  --font-head: 'Plus Jakarta Sans', sans-serif;
  --font-body: 'Inter', sans-serif;
}
```

### Sections
Each section in `index.html` is clearly delimited by a comment banner
(`HERO`, `ABOUT`, `SERVICES`, `PORTFOLIO`, `PRICING`, `TESTIMONIALS`,
`CONTACT`, `FOOTER`). Edit the text/markup inside and you're done.

### Images
The demo uses CSS gradients as lightweight placeholders so the template works
offline. To use real photos, replace the `.about__image` and
`.portfolio__thumb--*` backgrounds in `style.css`, or drop `<img>` tags into the
markup and point them at files in `assets/images/`.

### Contact form
The form is validated on the client but has **no backend**. To make it send
email, point the `<form>` `action` at your endpoint (e.g. Formspree, Netlify
Forms, or your own API) and remove the `e.preventDefault()` demo handling in
`initForms()` inside `assets/js/main.js`.

---

## 📑 Sections Included

| Section       | Description                                            |
|---------------|--------------------------------------------------------|
| Hero          | Headline, sub-copy, CTAs and animated stat counters    |
| About         | Story, checklist of strengths and experience badge     |
| Services      | Six icon cards describing your offerings                |
| Portfolio     | Filterable project gallery (All / Web / Branding / App) |
| Pricing       | Three-tier plan table with a highlighted "Pro" plan     |
| Testimonials  | Auto-playing slider with client quotes                  |
| Contact       | Contact details + validated message form                |
| Footer        | Link columns, newsletter signup and social icons        |

---

## 🌐 Browser Support

Works in all modern browsers (Chrome, Firefox, Safari, Edge). Graceful
fallbacks are provided where `IntersectionObserver` is unavailable.

---

## ✅ Validation

- **HTML** — valid HTML5 (W3C Markup Validation Service)
- **CSS** — valid CSS3
- Tested for responsiveness across mobile, tablet and desktop breakpoints
  (320px / 375px / 768px / 1024px / 1440px).

---

## 📄 License

Released under the [MIT License](LICENSE). Free for personal and commercial use.

---

Made with ❤️ &nbsp;— **Pinnacle** Template.
