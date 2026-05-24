# iCapital Wyoming LLC — WordPress Build

Complete production-ready WordPress architecture.

## Directory Structure

```
wordpress-build/wp-content/
├── themes/icapital-wyoming/      ← Install as WordPress theme
└── plugins/icapital-web3-core/   ← Install as WordPress plugin
```

## Installation Steps

### 1. WordPress Setup
Install WordPress locally (LocalWP recommended) or on your host. Point your domain to the WordPress root.

### 2. Install the Theme
Copy `themes/icapital-wyoming/` → `wp-content/themes/icapital-wyoming/`
Go to **WP Admin → Appearance → Themes** and activate **iCapital Wyoming LLC**.

### 3. Install the Plugin
Copy `plugins/icapital-web3-core/` → `wp-content/plugins/icapital-web3-core/`
Go to **WP Admin → Plugins** and activate **iCapital Web3 Core**.
This creates all 4 custom DB tables automatically.

### 4. Create Required Pages
In WP Admin → Pages, create these pages with the specified templates:

| Page Title      | Slug            | Template            |
|-----------------|-----------------|---------------------|
| Home            | (front page)    | (set as front page) |
| Login           | login           | Login Page          |
| Start / Register| start           | LLC Registration    |
| Dashboard       | dashboard       | Client Dashboard    |
| Secure Asset    | secure-asset    | Secure Asset Portal |
| Admin Portal    | admin-portal    | Admin Portal        |

Then: **Settings → Reading → Your homepage displays → Static page → Home**

### 5. Build CSS (Tailwind — optional)
```bash
cd themes/icapital-wyoming
npm install
npm run build:css
```

### 6. Build React App Bundle
```bash
cd plugins/icapital-web3-core/react-app
npm install
# Copy your existing Next.js src/ components into react-app/src/apps/
# Update all fetch() calls to use window.icapitalData.restUrl
npm run build
# Output: dist/icapital-app.iife.js
```

## REST API Endpoints

Base: `/wp-json/icapital/v1/`

| Method | Endpoint               | Auth     | Description              |
|--------|------------------------|----------|--------------------------|
| POST   | /login                 | Public   | Email/password login      |
| POST   | /register              | Public   | Create account + LLC      |
| POST   | /logout                | User     | Clear session             |
| GET    | /user                  | User     | Current user profile      |
| GET    | /llc-stats             | User     | LLC counts by status      |
| GET    | /llc-list              | User     | User's LLC applications   |
| GET    | /wallet/balances       | User     | Virtual balances + prices |
| GET    | /wallet/transactions   | User     | Transaction history       |
| POST   | /wallet/send           | User     | Initiate transfer         |
| POST   | /wallet/deposit        | User     | Confirm on-chain deposit  |
| POST   | /llc/register          | User     | New LLC registration      |
| GET    | /admin/users           | Admin    | All users                 |
| GET    | /admin/submissions     | Admin    | All LLC applications      |
| PATCH  | /admin/llc/{id}/status | Admin    | Update LLC status         |
| GET    | /admin/stats           | Admin    | Platform statistics       |
| GET    | /auth/siwe/nonce       | Public   | Get SIWE signing nonce    |
| POST   | /auth/siwe             | Public   | SIWE wallet login         |
| POST   | /auth/siwe/link        | User     | Link wallet to account    |

## Customizer Settings

Go to **Appearance → Customize → iCapital Wyoming LLC** to edit:
- Hero heading, subheading, CTA text + URL, quote
- Footer tagline and address

## WP Admin Menu
Go to **LLC Applications** in the WP admin sidebar to:
- View all LLC applications and update their status
- View all registered users and their wallet addresses
