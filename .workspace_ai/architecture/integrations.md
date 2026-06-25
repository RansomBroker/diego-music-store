# Integration Patterns

This document describes the external API patterns for the Diego Music Store ERP.

## 1. WhatsApp Gateway Integration
- **Purpose**: Automatic delivery of invoice PDFs to customers, payment reminders, promotional broadcasts, and PDF slips to employees.
- **Protocol**: HTTP REST API (Post request).
- **Format**:
  ```json
  {
    "phone": "081234567890",
    "message": "Terima kasih telah berbelanja di Diego Music Store...",
    "attachment_url": "https://erp.diegomusic.com/storage/invoices/INV-123.pdf"
  }
  ```

## 2. E-Commerce Marketplace Sync (Shopee & Tokopedia)
- **Inventory Sync (Outbound)**: Whenever stock level shifts at the central warehouse, trigger an API update patch to Marketplace item SKUs.
- **Order Imports (Inbound)**: Register webhooks to receive new order payloads. Automatically create POS invoice, deduct inventory, and post to accounting.
- **Mapping**: Store a mapping table `marketplace_mappings` correlating marketplace SKU codes to internal database SKUs.

## 3. Fingerprint Attendance Sync
- **Protocol**: API pull or CSV schedule import from fingerprint machine IP.
- **Mapping**: Matches device employee IDs to internal user IDs (`user_id`).
