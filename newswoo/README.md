# NewsWoo

![NewsWoo Logo](assets/logo.svg)

**WooCommerce, rebuilt for newsrooms.** A lean, purpose-built fork of WooCommerce designed as a drop-in replacement for [Newspack Bedrock](https://github.com/Postdated/Newspack) — stripped of retail bloat and optimized for digital subscriptions, paywall access, and reader donations.

## The NewsWoo + Newspack Stack

```
┌─────────────────────────────────────────────────┐
│  Newspack Bedrock (Postdated/Newspack)          │
│  ├── newspack-plugin   (content gates, paywall) │
│  ├── newspack-blocks   (modal checkout, donate) │
│  ├── newspack-popups   (reader conversion)      │
│  ├── newspack-newsletters (ESP sync)            │
│  └── ...17 more Newspack packages               │
│                                                  │
│  ┌─────────────────────────────────────────┐    │
│  │  NewsWoo  ← THIS REPO                   │    │
│  │  Drop-in replacement for WooCommerce     │    │
│  │  ├── Subscriptions (merged)              │    │
│  │  ├── Memberships (merged)                │    │
│  │  ├── Name Your Price (merged)            │    │
│  │  └── No shipping, no inventory, no bloat │    │
│  └─────────────────────────────────────────┘    │
└─────────────────────────────────────────────────┘
```

NewsWoo maintains full API compatibility with Newspack's WooCommerce integration layer — all the hooks, filters, and class interfaces that `newspack-plugin` and `newspack-blocks` depend on continue to work.

## Why NewsWoo?

Standard WooCommerce ships with 800,000+ lines of code built for physical retail. Newsrooms don't need shipping zones, inventory management, warehouse logistics, or complex product variations.

NewsWoo keeps what publishers need and removes what they don't:

| Feature | Standard WooCommerce | NewsWoo |
|---------|---------------------|---------|
| Primary Product | Physical or Digital Goods | Subscriptions, Paywall Access, Donations |
| Checkout Flow | Cart → Shipping → Billing → Payment | Direct Paywall → Modal Checkout |
| Database Weight | Heavy tables for stock, logs, shipping | Lightweight tables focused on user meta & access |
| Tax Engine | Shipping address destination | Billing address / Country residency only |
| Newspack Integration | Requires compatibility layer | Native — built for Newspack's hooks |

## What Gets Removed

- **Shipping & Fulfillment** — Shipping zones, classes, methods, calculators, address fields
- **Physical Inventory** — Stock management, backorders, low-stock alerts, product dimensions
- **Retail Checkout** — Cart page, "Add to Cart" fragments, complex coupon logic
- **Retail Product Types** — Grouped products, external/affiliate products, complex attribute variations
- **Tax Overhead** — Shipping-based tax calculations, geo-location for freight estimation
- **Setup Wizard** — Suppresses WC's first-run wizard (Newspack configures WC itself)

## What Gets Kept (and Enhanced)

- **Customer Accounts / My Account** — Reader dashboard for credit card updates, invoices, subscription management
- **Payment Gateways** — Stripe, PayPal, Apple Pay / Google Pay
- **Webhooks & REST API** — Mailchimp, ActiveCampaign, CRM integrations
- **Subscriptions** — Recurring access, tiered paywalls, trial periods, switching
- **Memberships** — Content restriction, member-only areas
- **Name Your Price** — Flexible donation and "pay what you want" support
- **Modal Checkout** — Compatible with Newspack's streamlined checkout modal
- **Content Gating** — Works with Newspack's metering and paywall system

## Newspack Integration Points

NewsWoo must maintain compatibility with these Newspack classes:

| Newspack Class | Integration | Status |
|----------------|-------------|--------|
| `WooCommerce_Connection` | Subscription statuses, order hooks, payment tokens | ✅ Compatible |
| `WooCommerce_Checkout` | Payment heading, checkout flow | ✅ Compatible |
| `Modal_Checkout` (newspack-blocks) | Streamlined modal purchase flow | ✅ Compatible |
| `Content_Gate` | Paywall and metering system | ✅ Compatible |
| `Metering` | Free article counting | ✅ Compatible |
| `Contact_Sync_Connector` | ESP data events from orders/subscriptions | ✅ Compatible |
| `WooCommerce_Cover_Fees` | Processing fee coverage | ✅ Compatible |
| `WooCommerce_Product_Validator` | Product validation rules | ✅ Compatible |

## Included Plugins (Analyzed)

| Plugin | Version | LOC | Purpose |
|--------|---------|-----|---------|
| WooCommerce | 11.1.1 | 800,537 | Core platform (to be trimmed) |
| WooCommerce Subscriptions | 9.2.0 | 155,157 | Recurring billing and access |
| WooCommerce Memberships | 1.30.0 | 146,424 | Content restriction and member areas |
| Name Your Price | 3.8.2 | 10,325 | Flexible/donation pricing |
| Subscriptions Gifting | 2.9.1 | 7,092 | Gift subscription support |

## Brand Assets

- [Logo (light)](assets/logo.svg)
- [Logo (dark)](assets/logo-dark.svg)
- [Icon](assets/icon.svg)
- [WooCommerce Icon Font](assets/fonts/WooCommerce.woff2)
- [Inter Font (WooCommerce's typeface)](assets/fonts/Inter-VariableFont_slnt,wght.woff2)

## Project Status

📋 See [ROADMAP.md](ROADMAP.md) for the full development plan.

## Development

See [dev/readme.md](dev/readme.md) for the development environment setup.

## License

GPL-2.0-or-later (same as WooCommerce)

## Links

- [NewsWoo Repository](https://github.com/Postdated/NewsWoo)
- [Newspack Bedrock](https://github.com/Postdated/Newspack)
- [Newspack (upstream)](https://newspack.com/)
- [WooCommerce](https://woocommerce.com/)

