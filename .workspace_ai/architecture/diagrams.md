# Architecture Diagrams

## 1. Offline POS FIFO Sync Queue
This diagram shows the Service Worker flow capturing requests offline and syncing them back sequentially when connectivity resumes.

```mermaid
sequenceDiagram
  autonumber
  actor Cashier as Kasir/POS UI
  participant SW as Service Worker
  participant DB as IndexedDB (FIFO)
  participant API as Backend Server API

  Cashier->>SW: Submit Transaction
  alt Network Online
    SW->>API: Post transaction directly
    API-->>Cashier: Return success & print receipt
  else Network Offline
    SW->>DB: Store transaction payload in queue
    DB-->>Cashier: Return cached success & print offline receipt
  end

  Note over SW, API: Reconnection Triggered
  loop For each item in IndexedDB queue (FIFO)
    SW->>API: Post transaction payload
    API->>API: Process inventory deduction & journal
    API-->>SW: Confirm success
    SW->>DB: Delete processed payload
  end
```

## 2. Double-Entry Accounting Engine Flow
This flowchart shows how business actions map automatically to double-entry ledger columns.

```mermaid
flowchart TD
  Action[Business Action: POS/PO/Payroll] --> Engine{Journal Engine}
  Engine -->|POS Checkout| POS_Debit[Debit: Cash / Receivables]
  Engine -->|POS Checkout| POS_Credit[Credit: Revenue / PPN Liability]
  Engine -->|POS Checkout| Inventory_Debit[Debit: HPP Expense]
  Engine -->|POS Checkout| Inventory_Credit[Credit: Inventory Assets]

  Engine -->|Payroll Generate| Pay_Debit[Debit: Salary Expense]
  Engine -->|Payroll Generate| Pay_Credit[Credit: Cash / Bank]

  POS_Debit & POS_Credit & Inventory_Debit & Inventory_Credit & Pay_Debit & Pay_Credit --> Valid{Check Debit = Credit?}
  Valid -->|Yes| Save[Commit DB Transaction]
  Valid -->|No| Rollback[Rollback & Trigger Alert]
```
