<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Services\HeroImageStorage;
use App\Support\HeroLinkOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeHeroController extends Controller
{
    public function __construct(
        private readonly HeroImageStorage $heroImages,
    ) {}

    public function index(): View
    {
        $slides = HeroSlide::query()->orderBy('sort_order')->orderBy('id')->get();
        $settings = HeroSetting::current();

        return view('admin.home-hero.index', [
            'slides' => $slides,
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'interval_seconds' => ['required', 'integer', 'min:2', 'max:60'],
        ], [], [
            'interval_seconds' => 'seconds per slide',
        ]);

        HeroSetting::current()->update([
            'interval_ms' => $validated['interval_seconds'] * 1000,
        ]);

        return redirect()
            ->route('admin.home-hero.index')
            ->with('success', 'Rotator timing updated.');
    }

    public function create(): View
    {
        return view('admin.home-hero.create', [
            'slide' => new HeroSlide([
                'sort_order' => (HeroSlide::query()->max('sort_order') ?? 0) + 1,
                'is_active' => true,
                'bg' => 'linear-gradient(135deg, #2d6388 0%, #4079a5 55%, #5a96bc 100%)',
            ]),
            'linkOptionGroups' => HeroLinkOptions::optionGroups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSlide($request, true);

        if ($request->hasFile('image_file')) {
            $validated['image'] = $this->heroImages->store($request->file('image_file'));
        }

        unset($validated['image_file'], $validated['link_key'], $validated['custom_link_url']);
        HeroSlide::query()->create($validated);

        return redirect()
            ->route('admin.home-hero.index')
            ->with('success', 'Home banner slide added.');
    }

    public function edit(HeroSlide $heroSlide): View
    {
        return view('admin.home-hero.edit', [
            'slide' => $heroSlide,
            'linkOptionGroups' => HeroLinkOptions::optionGroups(),
        ]);
    }

    public function update(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $validated = $this->validateSlide($request, false);

        if ($request->hasFile('image_file')) {
            $this->heroImages->delete($heroSlide->image);
            $validated['image'] = $this->heroImages->store($request->file('image_file'));
        }

        unset($validated['image_file'], $validated['link_key'], $validated['custom_link_url']);
        $heroSlide->update($validated);

        return redirect()
            ->route('admin.home-hero.index')
            ->with('success', 'Home banner slide updated.');
    }

    public function destroy(HeroSlide $heroSlide): RedirectResponse
    {
        $this->heroImages->delete($heroSlide->image);
        $heroSlide->delete();

        return redirect()
            ->route('admin.home-hero.index')
            ->with('success', 'Home banner slide removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSlide(Request $request, bool $imageRequired): array
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'alt' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'subline' => ['nullable', 'string', 'max:500'],
            'cta' => ['nullable', 'string', 'max:128'],
            'link_key' => ['nullable', 'string', 'max:2000'],
            'custom_link_url' => ['nullable', 'string', 'max:500'],
            'bg' => ['required', 'string', 'max:512'],
            'is_active' => ['sometimes', 'boolean'],
            'fill_slide' => ['sometimes', 'boolean'],
            'image_file' => [
                $imageRequired ? 'required' : 'nullable',
                'image',
                'max:5120',
            ],
        ], [
            'custom_link_url.required' => 'Enter a custom URL path or full link.',
        ], [
            'sort_order' => 'display order',
            'alt' => 'image description',
            'headline' => 'headline',
            'subline' => 'subline',
            'cta' => 'button text',
            'link_key' => 'button link',
            'custom_link_url' => 'custom URL',
            'bg' => 'background color',
            'image_file' => 'banner image',
        ]);

        if (($validated['link_key'] ?? '_none') === '_custom' && trim((string) ($validated['custom_link_url'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'custom_link_url' => 'Enter a custom URL path or full link.',
            ]);
        }

        $link = HeroLinkOptions::parseKey(
            $validated['link_key'] ?? '_none',
            $validated['custom_link_url'] ?? null
        );

        $validated['route'] = $link['route'];
        $validated['route_params'] = $link['route_params'];
        $validated['is_active'] = $request->boolean('is_active');
        $validated['fill_slide'] = $request->boolean('fill_slide');

        return $validated;
    }
}
