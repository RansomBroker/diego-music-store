# System Architecture

## Architecture Overview
The Diego Music Store ERP uses a monolithic Model-View-Controller (MVC) architecture with service layers for third-party integrations and event listeners for automated accounting journals.

```mermaid
graph TD
  User[User Interface] --> Controller[Laravel Controller]
  Controller --> Service[Service Layer - WA/Marketplace]
  Controller --> Model[Eloquents/Models]
  Model --> DB[(MySQL SQL DB)]
  Controller --> Log[Logs / Auditing]
  Controller --> Event[Event Listeners - Journal Engine]
  Event --> Ledger[(General Ledger)]
```

## Multi-Tenant / Branch Isolation
- All operational tables (`penjualan`, `stok`, `jurnal_umum`, `pengeluaran`) contain a `cabang_id` column.
- Global scopes are implemented in Laravel models to automatically filter data based on the authenticated user's active branch.

## Offline Architecture
- Front Desk POS runs a **Service Worker** to cache UI assets.
- Transactions are queued locally in **IndexedDB** using a FIFO structure if connection drops.
- Upon reconnection, a synchronization script fires the queue back to the server in strict order to avoid inventory conflicts.
