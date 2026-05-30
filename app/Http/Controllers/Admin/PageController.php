<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageImageStorage;
use App\Support\PageHtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly PageImageStorage $pageImages,
    ) {}

    public function index(): View
    {
        $pages = Page::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.pages.index', [
            'pages' => $pages,
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page([
                'sort_order' => (Page::query()->max('sort_order') ?? 0) + 1,
                'is_published' => false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request, null);
        $validated['body'] = PageHtmlSanitizer::clean($validated['body']);

        Page::query()->create($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Static page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatePage($request, $page);
        $validated['body'] = PageHtmlSanitizer::clean($validated['body']);

        $page->update($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Static page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Static page removed.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:8192'],
        ]);

        $path = $this->pageImages->store($request->file('file'));

        return response()->json([
            'location' => asset($path),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request, ?Page $existing): array
    {
        $slugRule = Rule::unique('pages', 'slug');
        if ($existing) {
            $slugRule = $slugRule->ignore($existing->id);
        }

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['sometimes', 'boolean'],
        ], [], [
            'slug' => 'URL slug',
            'title' => 'title',
            'meta_description' => 'meta description',
            'body' => 'page body',
        ]);

        $validated['slug'] = Str::slug($validated['slug'], '-');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['meta_description'] = isset($validated['meta_description']) ? trim($validated['meta_description']) : null;
        if ($validated['meta_description'] === '') {
            $validated['meta_description'] = null;
        }

        return $validated;
    }
}
