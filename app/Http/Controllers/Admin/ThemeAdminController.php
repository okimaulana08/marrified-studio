<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\Event;
use App\Models\GiftAccount;
use App\Models\Invitation;
use App\Models\Section;
use App\Services\Themes\ThemeCloner;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ThemeAdminController extends Controller
{
    public function __construct(
        private readonly ThemeRegistry $registry,
    ) {}

    public function index(): View
    {
        return view('admin.themes.index');
    }

    public function create(): View
    {
        return view('admin.themes.create');
    }

    public function edit(string $slug): View
    {
        if ($this->registry->find($slug) === null) {
            abort(404);
        }

        return view('admin.themes.edit', ['slug' => $slug]);
    }

    public function preview(string $slug): View
    {
        $theme = $this->registry->find($slug) ?? abort(404);
        $invitation = $this->buildSampleInvitation($slug);

        return view('admin.themes.preview', compact('invitation', 'theme'));
    }

    public function clone(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'target_slug' => ['required', 'regex:/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/'],
        ]);

        app(ThemeCloner::class)->clone($slug, $validated['target_slug']);

        return redirect()->route('admin.themes.edit', $validated['target_slug'])
            ->with('flash_message', "Tema '{$slug}' berhasil diduplikasi ke '{$validated['target_slug']}'.")
            ->with('flash_type', 'success');
    }

    /**
     * Build an in-memory sample Invitation for preview rendering.
     * Nothing is persisted to the database.
     */
    private function buildSampleInvitation(string $slug): Invitation
    {
        $invitation = new Invitation([
            'slug' => 'preview-'.$slug,
            'theme_slug' => $slug,
            'religious_text' => [
                'ayat' => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ',
                'translation' => 'Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.',
                'source' => 'QS. Ar-Rum: 21',
            ],
        ]);
        $invitation->id = 0;

        $couple = new Couple([
            'bride_name' => 'Anindya Rahayu',
            'bride_nickname' => 'Anin',
            'groom_name' => 'Farhan Pratama',
            'groom_nickname' => 'Farhan',
        ]);

        $invitation->setRelation('couple', $couple);

        $events = collect([
            new Event([
                'type' => 'akad',
                'name' => 'Akad Nikah',
                'date' => now()->addMonth()->format('Y-m-d'),
                'time' => '08:00',
                'venue_name' => 'Masjid Al-Hidayah',
                'venue_address' => 'Jl. Sudirman No. 1, Jakarta',
                'sort_order' => 1,
            ]),
            new Event([
                'type' => 'resepsi',
                'name' => 'Resepsi Pernikahan',
                'date' => now()->addMonth()->format('Y-m-d'),
                'time' => '11:00',
                'venue_name' => 'Hotel Grand Ballroom',
                'venue_address' => 'Jl. Thamrin No. 5, Jakarta',
                'sort_order' => 2,
            ]),
        ]);

        $invitation->setRelation('events', $events);

        $storyEntries = [
            ['year' => '2019', 'title' => 'Pertama Bertemu', 'description' => 'Berkenalan di acara kampus.', 'photo_path' => null],
            ['year' => '2022', 'title' => 'Mulai Berpacaran', 'description' => 'Resmi menjadi pasangan setelah lulus.', 'photo_path' => null],
            ['year' => '2025', 'title' => 'Lamaran', 'description' => 'Lamaran sederhana di rumah keluarga.', 'photo_path' => null],
        ];

        $thanksContent = [
            'title' => 'Terima Kasih',
            'message' => 'Atas kehadiran dan doa restu yang telah diberikan, kami mengucapkan terima kasih yang sebesar-besarnya.',
            'signature' => 'Kami yang berbahagia,',
            'photo_path' => null,
        ];

        $countdownContent = [
            'title' => 'Hitung Mundur',
            'message' => 'Menanti hari bahagia bersama kalian.',
        ];

        $sections = collect([
            new Section(['type' => 'cover',     'variant' => null, 'sort_order' => 1,  'enabled' => true, 'content' => []]),
            new Section(['type' => 'quotes',    'variant' => null, 'sort_order' => 2,  'enabled' => true, 'content' => ['arabic' => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم', 'translation' => 'Dan di antara tanda-tanda kekuasaan-Nya', 'source' => 'QS. Ar-Rum: 21']]),
            new Section(['type' => 'couple',    'variant' => null, 'sort_order' => 3,  'enabled' => true, 'content' => []]),
            new Section(['type' => 'story',     'variant' => null, 'sort_order' => 4,  'enabled' => true, 'content' => ['entries' => $storyEntries]]),
            new Section(['type' => 'event',     'variant' => null, 'sort_order' => 5,  'enabled' => true, 'content' => []]),
            new Section(['type' => 'countdown', 'variant' => null, 'sort_order' => 6,  'enabled' => true, 'content' => $countdownContent]),
            new Section(['type' => 'gallery',   'variant' => null, 'sort_order' => 7,  'enabled' => true, 'content' => ['images' => []]]),
            new Section(['type' => 'gift',      'variant' => null, 'sort_order' => 8,  'enabled' => true, 'content' => []]),
            new Section(['type' => 'rsvp',      'variant' => null, 'sort_order' => 9,  'enabled' => true, 'content' => []]),
            new Section(['type' => 'guestbook', 'variant' => null, 'sort_order' => 10, 'enabled' => true, 'content' => []]),
            new Section(['type' => 'thanks',    'variant' => null, 'sort_order' => 11, 'enabled' => true, 'content' => $thanksContent]),
        ]);

        $giftAccounts = collect([
            new GiftAccount(['type' => 'bank', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Anindya Rahayu', 'sort_order' => 1]),
            new GiftAccount(['type' => 'ewallet', 'bank_name' => 'GoPay', 'account_number' => '0812-3456-7890', 'account_name' => 'Farhan Pratama', 'sort_order' => 2]),
        ]);

        $invitation->setRelation('sections', $sections);
        $invitation->setRelation('giftAccounts', $giftAccounts);
        $invitation->setRelation('guestbookMessages', collect());

        return $invitation;
    }
}
