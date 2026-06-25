# Project: ERP Diego Music Store

## Overview
The ERP Diego Music Store is an all-in-one business management platform designed to automate and integrate all operational aspects of a music instrument and services retail business.

## Tech Stack
- **Backend Core**: Laravel 12 (PHP)
- **Framework & UI Components**: Filament, Livewire
- **Core Frontend**: HTML, Vanilla Javascript, Vanilla CSS
- **Database**: MySQL (SQL)
- **Command Executions**:
  - Artisan commands: `./docker-artisan.sh <command>`
  - Composer commands: `./docker-composer.sh <command>`
- **Offline Reliability**: Service Workers + IndexedDB (FIFO Synchronization Queue)
- **Integrations**:
  - WhatsApp Gateway API for automated notifications (Invoice PDF, Reminders, Broadcasts)
  - Marketplace API (Shopee & Tokopedia) for real-time stock sync and sales integration
  - Fingerprint / Attendance hardware integration

## Project Scope
1. **Front Desk (POS)**: Retail sales, Mix Payments, Customer Poin/Loyalty, Cashier sessions, and Instrument Service tracking (Kanban).
2. **Back Office**: Multi-cabang settings, Inventory, Procurement (PO/DO), Accounting double-entry engine, Depreciation/disposition of assets, and HR/Payroll calculation.
3. **Owner Dashboard**: Visual analytical metrics (Pareto 80/20, Sales vs Purchases, Stock Turn Over).
4. **Sales Portal**: Target tracking, sales commission dashboard, and personal attendance check-in.
