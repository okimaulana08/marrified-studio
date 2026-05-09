<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\Event;
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

        $sections = collect([
            new Section(['type' => 'cover',     'variant' => null, 'sort_order' => 1, 'enabled' => true, 'content' => []]),
            new Section(['type' => 'quotes',    'variant' => null, 'sort_order' => 2, 'enabled' => true, 'content' => ['arabic' => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم', 'translation' => 'Dan di antara tanda-tanda kekuasaan-Nya', 'source' => 'QS. Ar-Rum: 21']]),
            new Section(['type' => 'couple',    'variant' => null, 'sort_order' => 3, 'enabled' => true, 'content' => []]),
            new Section(['type' => 'event',     'variant' => null, 'sort_order' => 4, 'enabled' => true, 'content' => []]),
            new Section(['type' => 'guestbook', 'variant' => null, 'sort_order' => 5, 'enabled' => true, 'content' => []]),
        ]);

        $invitation->setRelation('sections', $sections);
        $invitation->setRelation('giftAccounts', collect());

        return $invitation;
    }
}
