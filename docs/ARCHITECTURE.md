# Architecture

## Repository layout

- `backend/` — Laravel 12 website, REST API and Super Admin
- `apps/customer/` — Flutter customer app
- `apps/seller/` — Flutter seller app
- `apps/delivery/` — Flutter delivery-partner app
- `assets/branding/` — approved shared brand assets

## Core domains

1. Identity and access: customer, seller, delivery partner, staff and super admin.
2. Marketplace: businesses, outlets, catalogues, products, variants and inventory.
3. Commerce: carts, orders, payments, coupons, refunds and settlements.
4. Fulfilment: service zones, delivery quotes, assignments and live status.
5. Operations: approvals, commission rules, audit logs, reports and CMS.

## Reuse policy

Tested authentication, API-client, role, security and deployment patterns may be adapted from other C-Net projects. Domain data and project-specific student/library/course modules must never be copied.

