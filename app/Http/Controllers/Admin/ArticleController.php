<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Services\ArticleImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleImageStorage $articleImages,
    ) {}

    public function index(): View
    {
        $articles = Article::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article([
                'sort_order' => (Article::query()->max('sort_order') ?? 0) + 1,
                'is_published' => false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request, null);

        if ($request->hasFile('hero_image_file')) {
            $validated['hero_image'] = $this->articleImages->store(
                $request->file('hero_image_file'),
                $validated['slug']
            );
        }

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        unset($validated['hero_image_file'], $validated['remove_hero_image'], $validated['image_files']);
        $article = Article::query()->create($validated);

        $this->storeUploadedImages($request, $article);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article created.');
    }

    public function edit(Article $article): View
    {
        $article->load('images');

        return view('admin.articles.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validateArticle($request, $article);

        if ($request->boolean('remove_hero_image')) {
            $this->articleImages->delete($article->hero_image);
            $validated['hero_image'] = null;
        } elseif ($request->hasFile('hero_image_file')) {
            $this->articleImages->delete($article->hero_image);
            $validated['hero_image'] = $this->articleImages->store(
                $request->file('hero_image_file'),
                $article->slug
            );
        } else {
            unset($validated['hero_image']);
        }

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = $article->published_at ?? now();
        }

        if (! $validated['is_published']) {
            $validated['published_at'] = $validated['published_at'] ?? null;
        }

        unset($validated['hero_image_file'], $validated['remove_hero_image'], $validated['image_files']);
        $article->update($validated);

        $this->deleteMarkedImages($request, $article);
        $this->storeUploadedImages($request, $article);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article updated.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $article->update([
            'is_published' => true,
            'published_at' => $article->published_at ?? now(),
        ]);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article is now live on the Articles page.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->articleImages->delete($article->hero_image);

        foreach ($article->images as $image) {
            $this->articleImages->delete($image->image_path);
        }

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateArticle(Request $request, ?Article $existing): array
    {
        $slugRule = Rule::unique('articles', 'slug');
        if ($existing) {
            $slugRule = $slugRule->ignore($existing->id);
        }

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'author' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'hero_image_alt' => ['nullable', 'string', 'max:255'],
            'hero_image_file' => ['nullable', 'image', 'max:8192'],
            'remove_hero_image' => ['sometimes', 'boolean'],
            'image_files' => ['nullable', 'array'],
            'image_files.*' => ['image', 'max:8192'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ], [], [
            'slug' => 'URL id',
            'author' => 'author',
            'title' => 'title',
            'subtitle' => 'subtitle',
            'body' => 'article body',
            'hero_image_file' => 'featured image',
            'image_files' => 'additional images',
        ]);

        $validated['slug'] = Str::slug($validated['slug'], '-');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['subtitle'] = isset($validated['subtitle']) ? trim($validated['subtitle']) : null;
        $validated['hero_image_alt'] = isset($validated['hero_image_alt']) ? trim($validated['hero_image_alt']) : null;
        if ($validated['subtitle'] === '') {
            $validated['subtitle'] = null;
        }
        if ($validated['hero_image_alt'] === '') {
            $validated['hero_image_alt'] = null;
        }

        return $validated;
    }

    private function storeUploadedImages(Request $request, Article $article): void
    {
        if (! $request->hasFile('image_files')) {
            return;
        }

        $maxOrder = $article->images()->max('sort_order') ?? 0;

        foreach ($request->file('image_files') as $file) {
            if (! $file) {
                continue;
            }

            $maxOrder++;
            $article->images()->create([
                'image_path' => $this->articleImages->store($file, $article->slug),
                'sort_order' => $maxOrder,
            ]);
        }
    }

    private function deleteMarkedImages(Request $request, Article $article): void
    {
        $ids = $request->input('delete_image_ids', []);
        if (! is_array($ids) || $ids === []) {
            return;
        }

        $images = ArticleImage::query()
            ->where('article_id', $article->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($images as $image) {
            $this->articleImages->delete($image->image_path);
            $image->delete();
        }
    }
}
