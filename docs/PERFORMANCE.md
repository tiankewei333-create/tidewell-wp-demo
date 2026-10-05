# Performance

The site has two performance modes, switchable under *Tidewell › Performance mode* in the admin.
Both show the same pages with the same content; only how they are delivered changes.

- **Baseline** reproduces how many small-business WordPress sites look before anyone has tuned
  them. It is the "before" state.
- **Optimized** is the state after a maintenance pass, and the default.

Switching mode rebuilds the Elementor pages (so image widgets pick the right image size) and
clears Elementor's CSS cache.

## What changes

| Area | Baseline | Optimized |
|---|---|---|
| Images | Original JPEGs at full size (1152–1280px) everywhere | WebP sub-sizes with `srcset`/`sizes`: 768px for cards and content, 960px for hero and lightbox |
| Hero image | WordPress default loading | Always loaded eagerly with `fetchpriority="high"` (it is the LCP element) |
| Fonts | Inter from Google Fonts (extra DNS + connection to two domains) | Self-hosted Inter variable font, latin subset, `font-display: swap` |
| Icons | Font Awesome icon font | Inline SVG icons (Elementor *Inline Font Icons*) |
| Elementor markup | Legacy wrapper markup | Elementor *Optimized Markup* (fewer wrapper `<div>`s) |
| WooCommerce assets | CSS and JS (cart fragments, order attribution) on every page | Only on shop, product, cart, checkout, account and pricing pages |
| Contact Form 7 assets | On every page | Only on the booking and contact pages |
| Block library CSS | On every page | Removed on Elementor pages, which don't use blocks |
| Dashicons | Loaded for visitors | Logged-in users only |
| WordPress head extras | Emoji script and styles, oEmbed discovery, RSD, generator tag | Removed |

The code is in `wp-content/plugins/tidewell-site/includes/performance.php`.

## Results

Lighthouse 13.5 performance audit, mobile profile (simulated slow 4G, 4× CPU slowdown), headless
Chrome. Each page was measured three times per mode, as a logged-out visitor; the table shows the
median run.

| Page | Mode | Score | FCP | LCP | Speed Index | CLS | Page weight | Requests |
|---|---|---|---|---|---|---|---|---|
| Home | Baseline | 59 | 5.3 s | 7.2 s | 7.8 s | 0.005 | 1,086 KB | 57 |
| | **Optimized** | **72** | **2.9 s** | **5.2 s** | **5.2 s** | **0** | **686 KB** | **34** |
| Service page | Baseline | 60 | 5.1 s | 7.2 s | 7.2 s | 0.008 | 1,102 KB | 57 |
| | **Optimized** | **75** | **2.7 s** | **5.0 s** | **5.0 s** | **0** | **691 KB** | **32** |
| Portfolio | Baseline | 63 | 3.9 s | 6.2 s | 7.1 s | 0.003 | 970 KB | 50 |
| | **Optimized** | **75** | **2.6 s** | **5.1 s** | **4.9 s** | **0** | **705 KB** | **30** |

Total Blocking Time was 0 ms in every run.

Across the three pages the optimized mode:

- cuts page weight by 27–37% and the number of requests by 40–44%;
- puts the first content on screen 1.3–2.4 s sooner (FCP −33 to −47%);
- shows the main image 1.1–2.2 s sooner (LCP −18 to −31%);
- removes layout shift entirely.

### Why the scores aren't in the 90s

The demo runs on WordPress Playground, which executes PHP as WebAssembly. Every page takes about
**2.6 s just to start responding** (Time To First Byte, medians of 2.6–2.8 s on every page in both
modes), and Lighthouse adds its own simulated network latency on top. FCP and LCP can never be
lower than that server time, which caps the score. The table compares the two modes on the same
server, so the differences come from the front-end changes alone.

On ordinary hosting with page caching the server responds much faster, so the absolute numbers
should improve considerably there. How much has to be measured on the real host; these results
don't predict it.

### Notes on the measurements

- **Final URLs only.** The service page is measured at `/services/deep-cleaning-portland/`. An
  older slug redirects there, and following that redirect produced a logged-in measurement.
- **Logged-out visitor.** The online demo blueprint logs every new visitor in as admin, which adds
  the admin bar and its assets. The measurement sends the cookie
  `playground_auto_login_already_happened=1` to skip that and measure what a customer sees.
- **Warm cache.** Each page was requested once before measuring, so the results don't include
  first-request PHP warm-up.

## Reproducing

1. Start the site locally (`npm run dev`, see the README). You are logged in automatically.
2. Under *Tidewell › Performance mode* choose **Baseline** and save. The pages are rebuilt.
3. Create a headers file that makes Lighthouse visit as a logged-out guest:

   ```bash
   echo '{"Cookie": "playground_auto_login_already_happened=1"}' > lh-headers.json
   ```

4. Load each page once to warm it up, then run Lighthouse three times per page and take the median:

   ```bash
   npx lighthouse http://127.0.0.1:9400/ --only-categories=performance --extra-headers=lh-headers.json --chrome-flags="--headless=new"
   npx lighthouse http://127.0.0.1:9400/services/deep-cleaning-portland/ --only-categories=performance --extra-headers=lh-headers.json --chrome-flags="--headless=new"
   npx lighthouse http://127.0.0.1:9400/portfolio/ --only-categories=performance --extra-headers=lh-headers.json --chrome-flags="--headless=new"
   ```

5. Switch to **Optimized** and repeat step 4.

Once the site is on a public host, PageSpeed Insights runs the same Lighthouse audit and adds
real-visitor data when Google has enough of it.
