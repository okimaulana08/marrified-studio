<div class="rsvp-form-wrap">
    @if ($submitted)
        <div class="rsvp-success">
            <p>✓ Terima kasih, konfirmasi Anda sudah kami terima.</p>
        </div>
    @else
        <form wire:submit="submit" class="rsvp-form">
            <label class="form-field">
                <span class="form-label">Nama Lengkap</span>
                <input type="text" wire:model="name" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </label>

            <label class="form-field">
                <span class="form-label">No. WhatsApp</span>
                <input type="tel" wire:model="phone" placeholder="08...">
                @error('phone') <span class="form-error">{{ $message }}</span> @enderror
            </label>

            <fieldset class="form-field">
                <legend class="form-label">Kehadiran</legend>
                <div class="form-radio-group">
                    <label><input type="radio" wire:model="attendance" value="attending"> Hadir</label>
                    <label><input type="radio" wire:model="attendance" value="not_attending"> Tidak Hadir</label>
                    <label><input type="radio" wire:model="attendance" value="maybe"> Mungkin</label>
                </div>
                @error('attendance') <span class="form-error">{{ $message }}</span> @enderror
            </fieldset>

            <label class="form-field">
                <span class="form-label">Jumlah Tamu</span>
                <input type="number" wire:model="partySize" min="1" max="10">
                @error('partySize') <span class="form-error">{{ $message }}</span> @enderror
            </label>

            <label class="form-field">
                <span class="form-label">Pesan / Catatan (opsional)</span>
                <textarea wire:model="note" rows="3"></textarea>
                @error('note') <span class="form-error">{{ $message }}</span> @enderror
            </label>

            <button type="submit" class="form-submit">Kirim Konfirmasi</button>
        </form>
    @endif
</div>
