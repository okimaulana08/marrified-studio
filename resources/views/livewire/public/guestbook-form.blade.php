<div class="guestbook-form-wrap no-swipe">
    <form wire:submit="submit" class="guestbook-form">
        <label class="form-field">
            <span class="form-label">Nama</span>
            <input type="text" wire:model="name" required>
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </label>

        <label class="form-field">
            <span class="form-label">Ucapan &amp; Doa</span>
            <textarea wire:model="message" rows="3" required placeholder="Tuliskan doa terbaikmu..."></textarea>
            @error('message') <span class="form-error">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="form-submit">Kirim Ucapan</button>

        @if ($submitted)
            <p class="form-success">✓ Ucapan berhasil dikirim.</p>
        @endif
    </form>
</div>
