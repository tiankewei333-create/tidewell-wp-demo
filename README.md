# Tidewell Cleaning Co. — WordPress + Elementor demo

A complete small-business website for a fictional home cleaning company in Portland, built on
WordPress, Elementor and WooCommerce. It is a portfolio piece: everything you see is set up by code,
so the whole site can be rebuilt from scratch in a few minutes, in the browser, with no hosting.

**[Open the live demo in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/tiankewei333-create/tidewell-wp-demo/main/blueprint.json)**
(first load takes about a minute while Playground installs WordPress and the plugins; you are logged in as admin)

> Tidewell Cleaning Co. is not a real business. Names, prices, reviews and addresses are invented.

## What's in it

- **Elementor pages** — home, services (with one page per service), pricing, about, FAQ, contact and
  a booking page, all on a Hello Elementor child theme with a shared design kit (colours, Inter
  typography, 1200px container).
- **WooCommerce** — cleaning packages with variations (home size, frequency), a gift card and a
  "pay after your clean" checkout.
- **Booking & contact forms** — Contact Form 7, with every submission stored in Flamingo so no
  enquiry is lost if email delivery fails.
- **Portfolio** — a custom post type with project types, a filterable gallery, a lightbox and a
  before/after slider on each project page.
- **Blog** — three articles, plus a "latest posts" block on the home page.
- **SEO** — Yoast titles and descriptions, LocalBusiness / Service structured data.
- **Business details in one place** — phone, email, address and hours are edited once under
  *Tidewell › Business details* and reused across the header, footer, pages and schema.
- **Performance mode switch** — the same site in a "typical unoptimised" state and an optimised
  state, with Lighthouse measurements of both. See [docs/PERFORMANCE.md](docs/PERFORMANCE.md).

## Documentation

| Document | For |
|---|---|
| [CLIENT-GUIDE.md](docs/CLIENT-GUIDE.md) | The site owner: editing pages, projects, posts, prices, reading form submissions |
| [MAINTENANCE-CHECKLIST.md](docs/MAINTENANCE-CHECKLIST.md) | Monthly / quarterly upkeep and known gotchas |
| [MAINTENANCE-LOG.md](docs/MAINTENANCE-LOG.md) | Issues found during the QA pass and how each was fixed |
| [PERFORMANCE.md](docs/PERFORMANCE.md) | What the performance mode changes and the before/after Lighthouse results |

## Run it locally

Requires Node.js 20+.

```bash
npm install
npm run dev
```

The site starts at <http://127.0.0.1:9400> after about two minutes (the console prints `Ready!`).
The theme and the site plugin are mounted from this folder, so edits to PHP, CSS and JS show up on
reload. Every restart is a fresh install: the setup routine recreates all content.

`blueprint.local.json` is used for local development; `blueprint.json` is the online version and
pulls the theme and plugin from this repository's `main` branch.

## Project structure

```
blueprint.json              Online Playground blueprint (installs from GitHub)
blueprint.local.json        Local blueprint used by `npm run dev`
docs/                       Client guide, maintenance and performance docs
wp-content/
  themes/tidewell/          Hello Elementor child theme: header, footer, blog and project templates,
                            site.css, self-hosted Inter font
  plugins/tidewell-site/
    includes/               Business details, admin page, portfolio CPT, shortcodes, schema,
                            performance mode
    setup/                  One-time site setup: options, plugins config, Elementor kit and pages,
                            WooCommerce products, forms, demo content
    assets/                 Portfolio CSS/JS, project editor JS, demo images
```

## License

GPL-2.0-or-later. Photos in `assets/images` were generated for this demo.
