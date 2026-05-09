<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Guest;
use App\Models\GuestbookMessage;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Support\GuestToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoInvitationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@marrified.test'],
            [
                'name' => 'Studio Admin',
                'password' => Hash::make('admin'),
                'role' => UserRole::Admin,
            ],
        );

        $coupleUser = User::query()->updateOrCreate(
            ['email' => 'raka-dewi@marrified.test'],
            [
                'name' => 'Raka & Dewi',
                'password' => Hash::make('couple'),
                'role' => UserRole::Couple,
            ],
        );

        $invitation = Invitation::query()->updateOrCreate(
            ['slug' => 'raka-dewi'],
            [
                'user_id' => $coupleUser->id,
                'religion_type' => 'islam',
                'religious_text' => [
                    'ayat' => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً',
                    'source' => 'QS Ar-Rum: 21',
                    'translation' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu istri-istri dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antaramu rasa kasih dan sayang.',
                ],
                'theme_slug' => 'watercolor-lush',
                'customizations' => [],
            ],
        );

        Couple::query()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            [
                'bride_name' => 'Dewi Anggraini Putri',
                'bride_nickname' => 'Dewi',
                'bride_parents' => 'Bapak Surya Pratama & Ibu Ratna Wulandari',
                'bride_instagram' => '@dewi.anggraini',
                'groom_name' => 'Raka Mahendra Wijaya',
                'groom_nickname' => 'Raka',
                'groom_parents' => 'Bapak Hadi Wijaya & Ibu Sari Mahendra',
                'groom_instagram' => '@raka.mahendra',
            ],
        );

        $invitation->events()->delete();
        Event::query()->create([
            'invitation_id' => $invitation->id,
            'type' => 'akad',
            'name' => 'Akad Nikah',
            'date' => '2026-09-15',
            'time' => '08:00:00',
            'venue_name' => 'Masjid Al-Ikhsan Banyuajuh',
            'venue_address' => 'Jl. Trunojoyo No. 61, Banjarmasin Utara, Kalimantan Selatan',
            'maps_url' => 'https://maps.google.com/?q=Masjid+Al-Ikhsan+Banjarmasin',
            'sort_order' => 0,
        ]);
        Event::query()->create([
            'invitation_id' => $invitation->id,
            'type' => 'resepsi',
            'name' => 'Resepsi Pernikahan',
            'date' => '2026-09-15',
            'time' => '11:00:00',
            'venue_name' => 'Gedung Mahligai Pancasila',
            'venue_address' => 'Jl. Sultan Adam, Banjarmasin Utara, Kalimantan Selatan',
            'maps_url' => 'https://maps.google.com/?q=Gedung+Mahligai+Pancasila',
            'sort_order' => 1,
        ]);

        $invitation->giftAccounts()->delete();
        GiftAccount::query()->create([
            'invitation_id' => $invitation->id,
            'type' => 'bank',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Dewi Anggraini Putri',
            'sort_order' => 0,
        ]);
        GiftAccount::query()->create([
            'invitation_id' => $invitation->id,
            'type' => 'bank',
            'bank_name' => 'Mandiri',
            'account_number' => '0987654321',
            'account_name' => 'Raka Mahendra Wijaya',
            'sort_order' => 1,
        ]);

        $invitation->sections()->delete();
        $sectionTypes = ['cover', 'quotes', 'couple', 'event', 'gallery', 'gift', 'rsvp', 'guestbook'];
        $variants = [
            'cover' => 'arch',
            'quotes' => 'default',
            'couple' => 'side-by-side',
            'event' => 'card',
            'gallery' => 'grid',
            'gift' => 'cashless-modal',
            'rsvp' => 'default',
            'guestbook' => 'default',
        ];
        foreach ($sectionTypes as $i => $type) {
            Section::query()->create([
                'invitation_id' => $invitation->id,
                'type' => $type,
                'variant' => $variants[$type],
                'sort_order' => $i,
                'enabled' => true,
            ]);
        }

        $invitation->guests()->delete();
        $guests = [
            ['name' => 'Pak Budi Hartono', 'relation' => 'Bapak', 'phone' => '081234567001'],
            ['name' => 'Bu Siti Nurhaliza', 'relation' => 'Ibu', 'phone' => '081234567002'],
            ['name' => 'Ahmad Rizky', 'relation' => 'Saudara', 'phone' => '081234567003'],
            ['name' => 'Maya Pertiwi', 'relation' => 'Saudari', 'phone' => '081234567004'],
            ['name' => 'Bapak/Ibu Sutrisno', 'relation' => 'Bapak/Ibu', 'phone' => '081234567005'],
        ];
        foreach ($guests as $g) {
            Guest::query()->create([
                'invitation_id' => $invitation->id,
                'name' => $g['name'],
                'relation' => $g['relation'],
                'phone' => $g['phone'],
                'token' => GuestToken::ensureUnique(),
            ]);
        }

        $invitation->guestbookMessages()->delete();
        GuestbookMessage::query()->create([
            'invitation_id' => $invitation->id,
            'name' => 'Pak Budi Hartono',
            'message' => 'Selamat menempuh hidup baru, semoga Sakinah Mawaddah Warahmah.',
            'is_visible' => true,
        ]);
        GuestbookMessage::query()->create([
            'invitation_id' => $invitation->id,
            'name' => 'Bu Siti Nurhaliza',
            'message' => 'Barakallahu lakuma, semoga selalu dalam lindungan Allah SWT.',
            'is_visible' => true,
        ]);

        $this->command?->info('Demo invitation seeded: /raka-dewi');
        $this->command?->info('First guest token: '.$invitation->guests->first()?->token);
        $this->command?->info('Admin login:  '.$admin->email.' / admin');
        $this->command?->info('Couple login: '.$coupleUser->email.' / couple');
    }
}
