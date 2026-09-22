<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use App\Models\Branch;
use App\Helpers\BranchHelper;
use App\Helpers\FonnteHelper;
use App\Actions\Notification\BroadcastWhatsAppMessage;
use App\Actions\Notification\SendWhatsAppBillingReminder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosCustomers extends Component
{
    use WithPagination;

    // ── State Tabel & Pencarian ──────────────────────────────────────────
    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    // ── State Modal ──────────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $isEditing  = false;
    public ?int $editingId  = null;

    // ── State Form ───────────────────────────────────────────────────────
    public string $name             = '';
    public string $phone            = '';
    public string $email            = '';
    public string $address          = '';
    public string $date_of_birth    = '';
    public ?int   $customer_label_id = null;
    public ?int   $pricing_tier_id   = null;
    public bool   $is_loyalty_member = false;
    public int    $loyalty_points    = 0;

    // ── State Konfirmasi Hapus ───────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId      = null;

    // ── State Broadcast WhatsApp ──────────────────────────────────────────
    public bool $showBroadcastModal = false;
    public string $broadcastTarget = 'all'; // 'all', 'loyalty_members', 'label', 'tier'
    public ?int $broadcastLabelId = null;
    public ?int $broadcastTierId = null;
    public string $broadcastMessage = '';

    // ── State Tagihan WhatsApp ────────────────────────────────────────────
    public bool $showBillingModal = false;
    public ?int $selectedBillingCustomerId = null;
    public ?Customer $billingCustomer = null;
    public string $billingMessagePreview = '';

    // ── State Pesan Khusus Pelanggan WhatsApp ─────────────────────────────
    public bool $showCustomerMessageModal = false;
    public ?int $selectedCustomerId = null;
    public ?Customer $selectedCustomer = null;
    public string $customerMessage = '';

    // ── Lifecycle ────────────────────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    // ── Sorting ──────────────────────────────────────────────────────────
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ── Modal: Buka Form Tambah ──────────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset(['name','phone','email','address','date_of_birth',
                       'customer_label_id','pricing_tier_id','is_loyalty_member','loyalty_points',
                       'editingId','isEditing']);
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->editingId          = $id;
        $this->isEditing          = true;
        $this->name               = $customer->name;
        $this->phone              = $customer->phone ?? '';
        $this->email              = $customer->email ?? '';
        $this->address            = $customer->address ?? '';
        $this->date_of_birth      = $customer->date_of_birth?->format('Y-m-d') ?? '';
        $this->customer_label_id  = $customer->customer_label_id;
        $this->pricing_tier_id    = $customer->pricing_tier_id;
        $this->is_loyalty_member  = (bool) $customer->is_loyalty_member;
        $this->loyalty_points     = (int) $customer->loyalty_points;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20|unique:customers,phone,' . ($this->editingId ?? 'NULL'),
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string|max:500',
            'date_of_birth'   => 'nullable|date',
            'loyalty_points'  => 'integer|min:0',
        ], [
            'name.required'   => 'Nama pelanggan wajib diisi.',
            'phone.unique'    => 'Nomor telepon sudah digunakan pelanggan lain.',
            'email.email'     => 'Format email tidak valid.',
            'loyalty_points.min' => 'Poin tidak boleh negatif.',
        ]);

        $data = [
            'name'              => $this->name,
            'phone'             => $this->phone ?: null,
            'email'             => $this->email ?: null,
            'address'           => $this->address ?: null,
            'date_of_birth'     => $this->date_of_birth ?: null,
            'customer_label_id' => $this->customer_label_id,
            'pricing_tier_id'   => $this->pricing_tier_id,
            'is_loyalty_member' => $this->is_loyalty_member,
            'loyalty_points'    => $this->loyalty_points,
        ];

        if ($this->isEditing) {
            Customer::findOrFail($this->editingId)->update($data);
            Notification::make()->title('Pelanggan Diperbarui')->success()->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Pelanggan berhasil diperbarui.',
            ]);
        } else {
            Customer::create($data);
            Notification::make()->title('Pelanggan Ditambahkan')->success()->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Pelanggan baru berhasil ditambahkan.',
            ]);
        }

        $this->showModal = false;
        $this->resetPage();
    }

    // ── Konfirmasi Hapus ─────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        Customer::findOrFail($this->deletingId)->delete();

        Notification::make()->title('Pelanggan Dihapus')->success()->send();
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Pelanggan berhasil dihapus.',
        ]);

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── WhatsApp Broadcast Handlers ──────────────────────────────────────
    public function openBroadcastModal(): void
    {
        if (empty($this->broadcastMessage)) {
            $this->applyTemplate('promo');
        }
        $this->showBroadcastModal = true;
    }

    public function applyTemplate(string $type): void
    {
        switch ($type) {
            case 'promo':
                $this->broadcastMessage = "Halo {nama},\n\nDapatkan promo diskon spesial hingga 25% untuk berbagai alat musik dan aksesoris pilihan di {toko}! Promo berlaku terbatas minggu ini. Kunjungi toko kami atau hubungi kasir untuk info selengkapnya.";
                break;
            case 'sale':
                $this->broadcastMessage = "Hai {nama},\n\nJangan lewatkan Weekend Sale spesial di {toko}! Dapatkan potongan harga eksklusif dan voucher belanja untuk setiap transaksi. Segera merapat ke toko kami ya!";
                break;
            case 'member':
                $this->broadcastMessage = "Halo Member {nama},\n\nSaat ini Anda memiliki total {poin} Poin Loyalitas di {toko}. Yuk segera tukarkan poin Anda dengan merchandise eksklusif atau diskon transaksi belanja berikutnya!";
                break;
        }
    }

    public function insertTag(string $tag): void
    {
        $this->broadcastMessage .= ($this->broadcastMessage ? ' ' : '') . $tag;
    }

    public function getEstimatedRecipientCountProperty(): int
    {
        $query = Customer::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($this->broadcastTarget === 'loyalty_members') {
            $query->where('is_loyalty_member', true);
        } elseif ($this->broadcastTarget === 'label' && $this->broadcastLabelId) {
            $query->where('customer_label_id', $this->broadcastLabelId);
        } elseif ($this->broadcastTarget === 'tier' && $this->broadcastTierId) {
            $query->where('pricing_tier_id', $this->broadcastTierId);
        }

        return $query->count();
    }

    public function sendBroadcast(): void
    {
        $this->validate([
            'broadcastMessage' => 'required|string|min:5',
        ], [
            'broadcastMessage.required' => 'Pesan broadcast wajib diisi.',
            'broadcastMessage.min' => 'Pesan broadcast minimal 5 karakter.',
        ]);

        $activeBranchId = BranchHelper::getActiveBranchId();
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();

        $action = new BroadcastWhatsAppMessage();
        $result = $action->execute(
            $this->broadcastMessage,
            $this->broadcastTarget,
            [
                'customer_label_id' => $this->broadcastLabelId,
                'pricing_tier_id'   => $this->broadcastTierId,
            ],
            $branch
        );

        if ($result['success']) {
            Notification::make()->title('Broadcast WhatsApp Selesai')->success()->body($result['message'])->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => $result['message'],
            ]);
            $this->showBroadcastModal = false;
        } else {
            Notification::make()->title('Broadcast Gagal')->danger()->body($result['message'])->send();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $result['message'],
            ]);
        }
    }

    // ── WhatsApp Tagihan Handlers ─────────────────────────────────────────
    public function openBillingModal(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->selectedBillingCustomerId = $customerId;
        $this->billingCustomer = $customer;

        $activeBranchId = BranchHelper::getActiveBranchId();
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();

        $action = new SendWhatsAppBillingReminder();
        $this->billingMessagePreview = $branch ? $action->buildMessage($customer, $branch) : '';

        $this->showBillingModal = true;
    }

    public function sendBillingReminder(): void
    {
        if (!$this->billingCustomer) {
            return;
        }

        $activeBranchId = BranchHelper::getActiveBranchId();
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();

        $action = new SendWhatsAppBillingReminder();
        $result = $action->execute($this->billingCustomer, $branch);

        if ($result['success']) {
            Notification::make()->title('Pengingat Tagihan Terkirim')->success()->body($result['message'])->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => $result['message'],
            ]);
            $this->showBillingModal = false;
        } else {
            Notification::make()->title('Gagal Kirim Tagihan')->danger()->body($result['message'])->send();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $result['message'],
            ]);
        }
    }

    // ── WhatsApp Pesan Khusus Pelanggan Handlers ──────────────────────────
    public function openCustomerMessageModal(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->applyCustomerTemplate('promo');
        $this->showCustomerMessageModal = true;
    }

    public function applyCustomerTemplate(string $type): void
    {
        if (!$this->selectedCustomer) {
            return;
        }

        $activeBranchId = BranchHelper::getActiveBranchId();
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        $storeName = $branch ? ($branch->store_name ?: ($branch->name ?: 'Diego Music Store')) : 'Diego Music Store';

        $name = $this->selectedCustomer->name;
        $points = number_format($this->selectedCustomer->loyalty_points ?? 0);

        switch ($type) {
            case 'promo':
                $this->customerMessage = "Halo {$name},\n\nDapatkan promo diskon spesial untuk produk alat musik dan aksesoris pilihan di {$storeName}! Kunjungi toko kami atau hubungi kami untuk informasi selengkapnya.";
                break;
            case 'sale':
                $this->customerMessage = "Hai {$name},\n\nJangan lewatkan penawaran spesial Weekend Sale dan potongan harga terbaik di {$storeName}. Segera kunjungi kami ya!";
                break;
            case 'member':
                $this->customerMessage = "Halo Member {$name},\n\nAnda saat ini memiliki total {$points} Poin Loyalitas di {$storeName}. Yuk manfaatkan poin Anda untuk potongan belanja atau reward menarik!";
                break;
        }
    }

    public function sendCustomerMessage(): void
    {
        $this->validate([
            'customerMessage' => 'required|string|min:5',
        ], [
            'customerMessage.required' => 'Pesan WhatsApp wajib diisi.',
            'customerMessage.min' => 'Pesan WhatsApp minimal 5 karakter.',
        ]);

        if (!$this->selectedCustomer || empty($this->selectedCustomer->phone)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Pelanggan tidak memiliki nomor WhatsApp yang valid.',
            ]);
            return;
        }

        $activeBranchId = BranchHelper::getActiveBranchId();
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();

        if (!$branch || !$branch->is_whatsapp_enabled) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Layanan WhatsApp cabang dinonaktifkan atau belum dikonfigurasi.',
            ]);
            return;
        }

        $token = FonnteHelper::resolveToken(null, $branch);
        $result = FonnteHelper::sendTextMessage(
            $this->selectedCustomer->phone,
            $this->customerMessage,
            $token,
            $branch
        );

        if (!empty($result['status'])) {
            Notification::make()->title('Pesan WhatsApp Terkirim')->success()->body("Pesan berhasil dikirim ke {$this->selectedCustomer->name}.")->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Pesan WhatsApp berhasil dikirim ke {$this->selectedCustomer->name}.",
            ]);
            $this->showCustomerMessageModal = false;
        } else {
            Notification::make()->title('Gagal Kirim WhatsApp')->danger()->body($result['message'] ?? 'Gagal mengirim pesan')->send();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $result['message'] ?? 'Gagal mengirim WhatsApp.',
            ]);
        }
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $customers = Customer::with(['label', 'pricingTier'])
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage > 0 ? $this->perPage : 1000);

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? \App\Models\Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-customers', [
            'customers'      => $customers,
            'labels'         => CustomerLabel::orderBy('name')->get(),
            'pricingTiers'   => PricingTier::orderBy('name')->get(),
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Data Pelanggan — POS']);
    }
}
