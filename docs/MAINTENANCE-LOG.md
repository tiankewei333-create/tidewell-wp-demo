# Maintenance log

QA and maintenance pass on the Tidewell site before handover. Every page was checked at desktop
(1440px), tablet (820px) and mobile (390px) widths, both forms were submitted, and a full
WooCommerce order was placed through checkout. Each entry records what was wrong, why, what changed
and how the fix was confirmed.

## Functional

### 1. "Order received" page redirected to the home page

- **Symptom:** after placing an order, customers landed on the home page instead of the order
  confirmation. *My account* sub-pages did the same.
- **Cause:** the setup routine sets pretty permalinks with `$wp_rewrite->set_permalink_structure()`.
  That call re-initialises WordPress's rewrite rules and silently drops the endpoints WooCommerce
  had already registered (`order-received`, `order-pay`, account endpoints), so the rules saved at
  the end of setup had no WooCommerce endpoints at all.
- **Fix:** re-register WooCommerce's endpoints straight after changing the permalink structure
  (`WC()->query->add_endpoints()`), then flush the rules (`setup/setup.php`).
- **Verified:** on a fresh install the order-received rule is present and both the confirmation page
  and *My account* return 200.
- **If it happens on a live site:** *Settings › Permalinks › Save changes* rebuilds the rules.

### 2. Booking and contact messages showed up as "[your-subject]" in Flamingo

- **Cause:** Flamingo titles each stored message from the form's `your-subject` field, which these
  forms don't have.
- **Fix:** each form now sets `flamingo_subject` (plus name and email) in its *Additional settings*,
  using the same subject as the notification email ("New booking request: [service] on
  [preferred-date]", "Website message from [your-name]").
- **Verified:** new submissions appear under *Flamingo › Inbound Messages* with readable titles.

### 3. Pages created by code rendered as plain text instead of the Elementor layout

- **Cause:** saving an Elementor document from code does not mark the page as "built with
  Elementor" (the editor does this before saving), so WordPress fell back to the raw post content.
- **Fix:** call `set_is_built_with_elementor( true )` before saving (`setup/pages.php`). The rebuild
  routine is also wrapped in an output buffer so Elementor's inline CSS output can't break the
  admin redirect after *Rebuild demo pages*.

### 4. Elementor page styles occasionally missing

- **Cause:** Elementor writes each page's CSS to a file in `uploads/elementor/css`; on this host
  the files were not always written, leaving pages unstyled until the cache was regenerated.
- **Fix:** *Elementor › Settings › Performance › CSS Print Method* set to **Internal Embedding**, so
  page CSS is printed in the page itself.

## Design and layout

### 5. Elementor's global link and button styles leaked into theme components

- **Symptom:** a teal background on the Services dropdown arrow, low-contrast link text in the
  top demo bar, restyled lightbox and filter buttons.
- **Cause:** the Elementor kit's link and button settings compile to `.elementor-kit-N a` and
  `.elementor-kit-N button`, which match every link and `<button>` on the site and outrank the
  theme's single-class selectors.
- **Fix:** link and button styles were removed from the kit; buttons and links are styled once in
  the theme's `site.css`. Kit colours and typography are still used by Elementor widgets.

### 6. Shop, cart, checkout and account pages were too narrow, or edge-to-edge on phones

- **Cause:** Hello Elementor limits pages that are not built with Elementor to 500–800px wide below
  1200px and gives them only 10px side padding.
- **Fix:** overrides in `site.css` so these pages use the site's 1200px container with 24px
  padding, and the page title lines up with the content.
- **Verified:** 24px side padding at 390px and 820px; content aligned with the header at 1440px.

### 7. WooCommerce product grid and buttons

- **Symptom:** uneven rows in the product grid; primary buttons ("Place order", "Add to cart") in
  WooCommerce's default purple.
- **Fix:** CSS grid for product lists so cards share a row height; primary buttons use the brand
  amber with dark text; variation dropdowns and quantity fields restyled to match the forms.

### 8. "Blog" highlighted in the menu on portfolio project pages

- **Cause:** WordPress marks the posts page as `current_page_parent` on every single post of any
  type, including portfolio projects.
- **Fix:** a `nav_menu_css_class` filter removes that class from Blog on project pages and marks
  Portfolio as the current section (`themes/tidewell/functions.php`).

### 9. Contact page form card stretched to the height of the text column

- **Fix:** `align-self: start` on the card so it keeps its natural height.

### 10. Blog index and articles not aligned with the header

- **Cause:** the blog container was 1100px wide while the rest of the site uses 1200px, and a
  `padding` shorthand on the article wrapper reset its side padding to zero on mobile.
- **Fix:** both use the shared `--tw-max` width and keep their side padding.
- **Verified:** blog content starts at the same x-position as the logo at all three widths.

### 11. Portfolio grid shifted while the page loaded

- **Cause:** the portfolio stylesheet was enqueued from inside the `[tidewell_portfolio]`
  shortcode. By then the page `<head>` has already been sent, so WordPress printed the stylesheet
  at the bottom of the page: the grid was laid out unstyled first, then jumped into place.
- **Fix:** the plugin now checks, before the `<head>` is printed, whether the page contains the
  shortcode (including inside Elementor data) and loads the stylesheet in the head
  (`includes/portfolio.php`). Affects the home, service and portfolio pages.

## Performance

The site's performance mode switch was used to measure the site before and after the front-end
work (image sizes and WebP, self-hosted font, SVG icons, plugin assets only where used). Lighthouse
mobile, median of three runs, logged-out visitor:

| Page | Score | LCP | Page weight | Requests |
|---|---|---|---|---|
| Home | 59 → 72 | 7.2 s → 5.2 s | 1,086 → 686 KB | 57 → 34 |
| Service page | 60 → 75 | 7.2 s → 5.0 s | 1,102 → 691 KB | 57 → 32 |
| Portfolio | 63 → 75 | 6.2 s → 5.1 s | 970 → 705 KB | 50 → 30 |

Layout shift dropped to 0 on all three pages and Total Blocking Time stayed at 0 ms. The demo host
(WordPress Playground) takes about 2.6 s to start responding to every request, which caps the
scores in both modes.

Full method and per-metric results: [PERFORMANCE.md](PERFORMANCE.md).
