<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SiteLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteLinkController extends Controller
{
    public function index(): View
    {
        $links = SiteLink::query()
            ->with('page')
            ->orderBy('placement')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->groupBy('placement');

        return view('admin.site-links.index', [
            'linksByPlacement' => $links,
            'placements' => SiteLink::PLACEMENTS,
        ]);
    }

    public function create(): View
    {
        return view('admin.site-links.create', [
            'link' => new SiteLink([
                'sort_order' => (SiteLink::query()->max('sort_order') ?? 0) + 1,
                'is_published' => true,
                'placement' => 'footer_left',
            ]),
            'pages' => $this->pageOptions(),
            'placements' => SiteLink::PLACEMENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SiteLink::query()->create($this->validateLink($request));

        return redirect()
            ->route('admin.site-links.index')
            ->with('success', 'Site link created.');
    }

    public function edit(SiteLink $siteLink): View
    {
        $siteLink->load('page');

        return view('admin.site-links.edit', [
            'link' => $siteLink,
            'pages' => $this->pageOptions(),
            'placements' => SiteLink::PLACEMENTS,
        ]);
    }

    public function update(Request $request, SiteLink $siteLink): RedirectResponse
    {
        $siteLink->update($this->validateLink($request));

        return redirect()
            ->route('admin.site-links.index')
            ->with('success', 'Site link updated.');
    }

    public function destroy(SiteLink $siteLink): RedirectResponse
    {
        $siteLink->delete();

        return redirect()
            ->route('admin.site-links.index')
            ->with('success', 'Site link removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLink(Request $request): array
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'destination_type' => ['required', Rule::in(['page', 'url'])],
            'page_id' => ['nullable', 'required_if:destination_type,page', 'integer', Rule::exists('pages', 'id')],
            'url' => ['nullable', 'required_if:destination_type,url', 'string', 'max:500'],
            'placement' => ['required', 'string', Rule::in(array_keys(SiteLink::PLACEMENTS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['sometimes', 'boolean'],
        ], [
            'page_id.required_if' => 'Choose a static page for this link.',
            'url.required_if' => 'Enter a custom URL for this link.',
        ], [
            'label' => 'link text',
            'page_id' => 'static page',
            'url' => 'custom URL',
            'placement' => 'location',
        ]);

        if ($validated['destination_type'] === 'page') {
            $validated['url'] = null;
        } else {
            $validated['page_id'] = null;
            $validated['url'] = trim((string) $validated['url']);
        }

        unset($validated['destination_type']);
        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Page>
     */
    private function pageOptions()
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'is_published']);
    }
}
