# MPRO Text Scramble

> Character decode animation for text, links, and buttons — part of the MPRO plugin suite for WordPress.

[![Version](https://img.shields.io/badge/version-1.0.0-black?style=flat-square)](https://github.com/moghadam-pro/text-scramble-wp-plugin/releases)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-3858e9?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/license-GPL--2.0-green?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Overview

**MPRO Text Scramble** adds a glitch/decode animation to any element on your WordPress site. When triggered, each character cycles through random symbols before snapping to its final form — creating the classic "decoding" effect seen on creative agency sites.

The effect is fully configurable from a dedicated settings panel under the **MPRO** admin menu, works sitewide via a CSS class, integrates directly into Elementor's widget panel, and also supports a shortcode for any other context.

---

## Preview

```
✦ Each character scrambles through random glyphs before resolving
   A stagger delay creates a left-to-right wave motion
   Trigger on page load (with IntersectionObserver) or on hover
   Works on headings, paragraphs, links, buttons — any text element
```

---

## Features

- **Zero dependencies** — pure vanilla JS, no jQuery or external libraries
- **Five built-in charsets** — Symbols, Uppercase, Alphanumeric, Block characters, Arabic — plus a fully custom charset field
- **Three trigger modes** — on page load, on hover only, or both (load + hover repeat)
- **IntersectionObserver support** — load trigger fires when the element enters the viewport, not on page ready
- **Per-character stagger** — configurable delay between each character starting, creating a natural wave effect
- **Elementor widget toggle** — enable per widget directly from the content tab (Heading, Button, Text Editor)
- **CSS class method** — add `mpro-scramble` to any element's Advanced → CSS Classes in Elementor
- **Per-element trigger override** — set `data-scramble-trigger="hover"` via Elementor Custom Attributes to override the global setting per element
- **Shortcode support** — `[mpro_scramble]` works inside any widget or the block editor
- **MPRO suite integration** — registers under the shared **MPRO** admin menu at sidebar position 4

---

## Installation

### From ZIP

1. Download `mpro-text-scramble.zip` from the [Releases](https://github.com/moghadam-pro/text-scramble-wp-plugin/releases) page
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Select the ZIP and click **Install Now**
4. Click **Activate Plugin**

### From Source

```bash
cd wp-content/plugins/
git clone https://github.com/moghadam-pro/text-scramble-wp-plugin.git mpro-text-scramble
```

Then activate from **Plugins → Installed Plugins**.

---

## Configuration

After activation, go to **MPRO → Text Scramble** in the WordPress admin sidebar.

### Charset

Controls which characters are used during the scramble animation.

| Option       | Characters used                               |
| ------------ | --------------------------------------------- | --- |
| Symbols      | `!@#$%^&\*()\_+-=[]{}                         | `   |
| Uppercase    | `A–Z`                                         |
| Alphanumeric | `A–Z`, `a–z`, `0–9`                           |
| Blocks       | `░▒▓█▄▀■□▪▫◆◇○●`                              |
| Arabic       | Arabic alphabet characters                    |
| Custom       | Any characters you define in the custom field |

### Trigger

| Option          | Behavior                                                                            |
| --------------- | ----------------------------------------------------------------------------------- |
| On page load    | Fires once when the element scrolls into view (IntersectionObserver, 15% threshold) |
| On hover only   | Fires each time the cursor enters the element                                       |
| On load + hover | Fires on scroll-in, then repeats on every hover                                     |

### Timing

| Setting             | Range       | Default | Description                                                     |
| ------------------- | ----------- | ------- | --------------------------------------------------------------- |
| Duration            | 100–1500 ms | `350`   | Total animation time per element                                |
| Iterations per char | 1–15        | `5`     | How many random characters each position shows before settling  |
| Stagger             | 0–100 ms    | `18`    | Delay between each character starting — controls the wave speed |

### CSS Class

The class name used to target elements sitewide. Default: `mpro-scramble`. Change this if it conflicts with an existing class in your theme.

### Load sitewide

When enabled (default), scripts are enqueued on all frontend pages. Disable if you only want the effect on specific pages and are using the shortcode method.

---

## Usage

### Method 1 — Elementor CSS Classes (recommended for most elements)

1. Select any widget in Elementor
2. Go to the **Advanced** tab
3. Under **CSS Classes**, type `mpro-scramble`
4. Update the page

Works with any widget that renders visible text.

### Method 2 — Elementor widget toggle (Heading, Button, Text Editor)

1. Edit a **Heading**, **Button**, or **Text Editor** widget
2. Scroll to the bottom of the **Content** tab
3. Find the **✦ MPRO Scramble** switcher and turn it On
4. Update the page

### Method 3 — Shortcode

Use inside any Text Editor widget, Classic Editor block, or shortcode-enabled field:

```
[mpro_scramble]Your text here[/mpro_scramble]
```

With optional attributes:

```
[mpro_scramble tag="span" trigger="hover"]Hover over me[/mpro_scramble]
[mpro_scramble tag="div" trigger="load"]Animates on scroll-in[/mpro_scramble]
```

| Attribute | Default        | Description                                                   |
| --------- | -------------- | ------------------------------------------------------------- |
| `tag`     | `span`         | HTML tag to wrap the content                                  |
| `trigger` | global setting | Override trigger for this element: `load`, `hover`, or `both` |
| `class`   | —              | Extra CSS classes to add alongside the scramble class         |

### Method 4 — Manual HTML

Add the class directly to any HTML element:

```html
<h1 class="mpro-scramble">Hello World</h1>
<a href="/contact" class="mpro-scramble" data-scramble-trigger="hover"
  >Contact</a
>
<button class="mpro-scramble" data-scramble-trigger="both">Get Started</button>
```

### Per-element trigger override

Any element can override the global trigger setting using the `data-scramble-trigger` attribute:

```
data-scramble-trigger="hover"
data-scramble-trigger="load"
data-scramble-trigger="both"
```

In Elementor, add this via **Advanced → Custom Attributes**.

---

## How It Works

### JS engine

On boot, the script queries all elements matching the configured CSS class selector. Elements with a `load` or `both` trigger are observed with `IntersectionObserver`; elements with `hover` or `both` get a `mouseenter` listener.

When triggered, `scrambleElement()` runs:

1. Reads the element's `textContent` and stores it in `data-mpro-original`
2. Replaces the inner HTML with individual `<span class="mpro-char">` wrappers, one per character
3. For each non-space span, sets an interval that replaces the character with a random one from the charset
4. After `iterations` ticks, the span resolves to the original character and the interval clears
5. Each character's interval is delayed by `index × stagger` ms, producing the wave

```js
// Core loop per character
var tick = setInterval(function () {
  count++;
  if (count >= iters) {
    clearInterval(tick);
    span.textContent = span.dataset.final;
    span.classList.remove("mpro-glitch");
  } else {
    span.textContent = randChar(charset);
  }
}, interval);
```

### Config injection

Settings are passed from PHP to JS via `wp_localize_script` as `window.mproScrambleConfig`:

```js
window.mproScrambleConfig = {
  charset: "!@#$%^&*()_+-=[]{}|",
  duration: 350,
  iterations: 5,
  stagger: 18,
  trigger: "load",
  cssClass: ".mpro-scramble",
};
```

### Elementor live preview

The script hooks into `elementorFrontend.hooks` so newly rendered widgets in the Elementor editor get the effect applied without a page reload:

```js
window.elementorFrontend.hooks.addAction(
  "frontend/element_ready/global",
  function ($scope) {
    // re-query and bind new elements inside $scope
  },
);
```

---

## File Structure

```
mpro-text-scramble/
├── mpro-text-scramble.php   # Main plugin file — menu, settings, shortcode, Elementor hooks
├── README.md
├── css/
│   └── mpro-text-scramble.css   # Minimal frontend styles (.mpro-char, .mpro-glitch)
└── js/
    └── mpro-text-scramble.js    # Frontend scramble engine (vanilla JS, no dependencies)
```

---

## Requirements

|           | Minimum                                                                            |
| --------- | ---------------------------------------------------------------------------------- |
| WordPress | 5.8                                                                                |
| PHP       | 7.4                                                                                |
| Browser   | Any modern browser with `IntersectionObserver` and `requestAnimationFrame` support |
| Elementor | 3.0+ (optional — not required for CSS class or shortcode methods)                  |

---

## MPRO Suite

This plugin is part of the **MPRO** collection — a set of custom WordPress plugins built for fine-grained creative control. All MPRO plugins share a single admin menu entry at sidebar position 4 and are designed to work alongside each other without conflicts.

| Plugin                                                                               | Description                                                |
| ------------------------------------------------------------------------------------ | ---------------------------------------------------------- |
| **Text Scramble**                                                                    | Character decode animation for text, links, and buttons    |
| [**Background Motion**](https://github.com/moghadam-pro/background-motion-wp-plugin) | Interactive pixel displacement canvas for site backgrounds |

More plugins coming soon.

---

## Changelog

### 1.0.0

- Initial release
- Five built-in charsets with custom charset support
- Three trigger modes: load, hover, both
- IntersectionObserver-based scroll-in detection
- Per-character stagger with configurable timing
- Elementor widget toggle for Heading, Button, Text Editor
- CSS class method works with any element in any page builder
- Shortcode with `tag`, `trigger`, and `class` attributes
- Per-element trigger override via `data-scramble-trigger`
- Elementor live preview support via `elementorFrontend.hooks`
- MPRO shared admin menu integration

---

## Author

**Moghadam.pro** — [moghadam.pro](https://moghadam.pro/mpro-plugins)

---

## License

Licensed under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html).
