<?php

namespace App\Support;

use App\Models\Article;
use App\Models\HeroSlide;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HeroLinkOptions
{
    /**
     * @return array<string, list<array{label: string, key: string}>>
     */
    public static function optionGroups(): array
    {
        $groups = [
            'Main pages' => [
                self::option('No link', '_none'),
                self::option('Home', 'home'),
                self::option('Gallery', 'gallery'),
                self::option('Search (blank form)', 'search'),
                self::option('History', 'history'),
                self::option('Articles index', 'articles.index'),
                self::option('Contribute', 'contribute'),
                self::option('My Collection', 'collection.index'),
            ],
        ];

        if (Schema::hasTable('pages')) {
            $pages = Page::query()
                ->where('is_published', true)
                ->orderBy('title')
                ->get(['title', 'slug']);

            if ($pages->isNotEmpty()) {
                $groups['Static pages'] = $pages
                    ->map(fn (Page $page) => self::option(
                        $page->title.' (/'.$page->slug.')',
                        self::makeKey('pages.show', ['slug' => $page->slug])
                    ))
                    ->all();
            }
        }

        $jurisdictions = DB::table('plates')
            ->whereNotNull('jurisdiction')
            ->where('jurisdiction', '!=', '')
            ->distinct()
            ->orderBy('jurisdiction')
            ->pluck('jurisdiction');

        if ($jurisdictions->isNotEmpty()) {
            $groups['Search by state'] = $jurisdictions
                ->map(fn (string $jurisdiction) => self::option(
                    'Search — '.$jurisdiction,
                    self::makeKey('search', ['jurisdiction' => $jurisdiction])
                ))
                ->all();
        }

        $setNames = DB::table('plates')
            ->whereNotNull('set_name')
            ->where('set_name', '!=', '')
            ->distinct()
            ->orderBy('set_name')
            ->pluck('set_name');

        if ($setNames->isNotEmpty()) {
            $groups['Gallery sets'] = $setNames
                ->map(fn (string $setName) => self::option(
                    $setName,
                    self::makeKey('gallery.show', ['setName' => $setName])
                ))
                ->all();
        }

        if (Schema::hasTable('articles')) {
            $articles = Article::query()
                ->where('is_published', true)
                ->orderBy('title')
                ->get(['title', 'slug']);

            if ($articles->isNotEmpty()) {
                $groups['Article pages'] = $articles
                    ->map(fn (Article $article) => self::option(
                        $article->title,
                        self::makeKey('articles.show', ['slug' => $article->slug])
                    ))
                    ->all();
            }
        }

        $groups['Other'] = [
            self::option('Custom URL…', '_custom'),
        ];

        return $groups;
    }

    public static function makeKey(string $route, array $params = []): string
    {
        if ($params === []) {
            return $route;
        }

        return 'b64:'.base64_encode(json_encode([
            'route' => $route,
            'params' => $params,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array{route: ?string, route_params: array<string, mixed>}
     */
    public static function parseKey(?string $key, ?string $customUrl = null): array
    {
        if (! $key || $key === '_none') {
            return ['route' => null, 'route_params' => []];
        }

        if ($key === '_custom') {
            $url = self::normalizeCustomUrl(trim((string) $customUrl));

            if ($url === '') {
                return ['route' => null, 'route_params' => []];
            }

            $searchLink = self::searchLinkFromUrl($url);
            if ($searchLink !== null) {
                return $searchLink;
            }

            return [
                'route' => null,
                'route_params' => ['_url' => $url],
            ];
        }

        if (str_starts_with($key, 'b64:')) {
            $payload = json_decode(base64_decode(substr($key, 4), true) ?: '', true);

            if (is_array($payload)) {
                $route = $payload['route'] ?? null;
                $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

                return [
                    'route' => $route,
                    'route_params' => self::normalizeSearchParams($route, $params),
                ];
            }
        }

        if (str_contains($key, '|')) {
            $segments = explode('|', $key);
            $route = array_shift($segments) ?: null;
            $params = [];

            foreach ($segments as $segment) {
                if (! str_contains($segment, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $segment, 2);
                $params[$name] = rawurldecode($value);
            }

            return [
                'route' => $route,
                'route_params' => self::normalizeSearchParams($route, $params),
            ];
        }

        return [
            'route' => $key,
            'route_params' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private static function normalizeSearchParams(?string $route, array $params): array
    {
        if ($route === 'search' && $params !== [] && ! isset($params['search'])) {
            return ['search' => '1'] + $params;
        }

        return $params;
    }

    public static function keyFromSlide(HeroSlide $slide): string
    {
        $params = $slide->route_params ?? [];

        if (! empty($params['_url'])) {
            return '_custom';
        }

        if ($slide->route === 'about') {
            return self::makeKey('pages.show', ['slug' => 'about']);
        }

        if (! $slide->route) {
            return '_none';
        }

        $params = collect($params)
            ->except(['_url', '_custom'])
            ->all();

        if ($slide->route === 'search') {
            unset($params['search']);
        }

        return self::makeKey($slide->route, $params);
    }

    public static function customUrlFromSlide(HeroSlide $slide): string
    {
        return (string) (($slide->route_params ?? [])['_url'] ?? '');
    }

    public static function labelForSlide(HeroSlide $slide): string
    {
        if ($slide->route === 'search' && ! empty($slide->route_params['jurisdiction'])) {
            return 'Search — '.$slide->route_params['jurisdiction'];
        }

        if ($slide->route === 'gallery.show' && ! empty($slide->route_params['setName'])) {
            return (string) $slide->route_params['setName'];
        }

        if ($slide->route === 'pages.show' && ! empty($slide->route_params['slug'])) {
            return 'Page: /'.$slide->route_params['slug'];
        }

        $targetKey = self::keyFromSlide($slide);

        if ($targetKey === '_custom') {
            $custom = self::customUrlFromSlide($slide);
            $searchLink = self::searchLinkFromUrl(self::normalizeCustomUrl($custom));
            if ($searchLink !== null && ! empty($searchLink['route_params']['jurisdiction'])) {
                return 'Search — '.$searchLink['route_params']['jurisdiction'];
            }

            return $custom !== '' ? 'Custom: '.$custom : 'Custom URL…';
        }

        foreach (self::optionGroups() as $options) {
            foreach ($options as $option) {
                if ($option['key'] === $targetKey) {
                    return $option['label'];
                }
            }
        }

        if ($slide->route) {
            return $slide->route;
        }

        return '—';
    }

    public static function normalizeCustomUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (! str_starts_with($url, '/search')) {
            return $url;
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $query = array_filter($query, static fn ($value) => $value !== '' && $value !== null);

        return $query === [] ? '/search' : '/search?'.http_build_query($query);
    }

    /**
     * @return array{route: string, route_params: array<string, mixed>}|null
     */
    public static function searchLinkFromUrl(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        if ($path !== '/search' && ! str_starts_with($url, '/search?')) {
            return null;
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $query = array_filter($query, static fn ($value) => $value !== '' && $value !== null);

        return [
            'route' => 'search',
            'route_params' => self::normalizeSearchParams('search', $query),
        ];
    }

    private static function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return rtrim((string) config('app.url'), '/').$url;
        }

        return url($url);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function urlFor(array $data): ?string
    {
        $route = $data['route'] ?? null;
        $params = $data['route_params'] ?? [];

        if ($route === 'about') {
            $route = 'pages.show';
            $params = ['slug' => 'about'];
        }

        if (! empty($params['_url'])) {
            $url = self::normalizeCustomUrl(trim((string) $params['_url']));

            if ($url === '') {
                return null;
            }

            $searchLink = self::searchLinkFromUrl($url);
            if ($searchLink !== null) {
                return route($searchLink['route'], $searchLink['route_params']);
            }

            return self::absoluteUrl($url);
        }

        if (! $route) {
            return null;
        }

        if ($route === 'collection.index' && ! Auth::check()) {
            return route('login');
        }

        $routeParams = collect($params)->except(['_url', '_custom'])->all();

        if ($route === 'search' && $routeParams !== [] && ! isset($routeParams['search'])) {
            $routeParams = ['search' => '1'] + $routeParams;
        }

        return route($route, $routeParams);
    }

    /**
     * @return array{label: string, key: string}
     */
    private static function option(string $label, string $key): array
    {
        return [
            'label' => $label,
            'key' => $key,
        ];
    }
}
