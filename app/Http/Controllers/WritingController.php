<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class WritingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Writing/Index', [
            'pieces' => $this->pieces(),
        ]);
    }

    public function show(string $slug): Response
    {
        $piece = collect($this->pieces())->firstWhere('slug', $slug);

        if (! $piece) {
            throw new NotFoundHttpException();
        }

        $path = resource_path('writing/' . $piece['file']);

        if (! File::exists($path)) {
            throw new NotFoundHttpException();
        }

        $cacheKey = 'writing.html.' . $piece['slug'] . '.' . filemtime($path);

        $html = Cache::remember($cacheKey, 3600, function () use ($path) {
            [, $body] = $this->splitFrontmatter(File::get($path));

            $environment = new Environment([
                'html_input' => 'allow',
                'allow_unsafe_links' => false,
            ]);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new GithubFlavoredMarkdownExtension());

            return (new MarkdownConverter($environment))->convert($body)->getContent();
        });

        return Inertia::render('Writing/Show', [
            'piece' => $piece,
            'html' => $html,
        ]);
    }

    /**
     * Public list of pieces. Metadata comes from YAML frontmatter when
     * present; fall back to the static block below for predictable
     * ordering and human-friendly labels.
     */
    private function pieces(): array
    {
        $files = [
            'substrate-is-the-agent.md' => [
                'slug' => 'substrate-is-the-agent',
                'kind' => 'Essay',
                'order' => 1,
            ],
            'substrate-is-the-body.md' => [
                'slug' => 'substrate-is-the-body',
                'kind' => 'Technical paper',
                'order' => 2,
            ],
            'the-substrate-dreams.md' => [
                'slug' => 'the-substrate-dreams',
                'kind' => 'Essay',
                'order' => 3,
            ],
        ];

        $pieces = [];

        foreach ($files as $file => $base) {
            $path = resource_path('writing/' . $file);
            if (! File::exists($path)) {
                continue;
            }

            [$front] = $this->splitFrontmatter(File::get($path));

            $pieces[] = array_merge($base, [
                'file' => $file,
                'title' => $front['title'] ?? $base['slug'],
                'subtitle' => $front['summary'] ?? '',
                'word_count' => number_format($front['word_count'] ?? 0),
                'reading_time' => isset($front['reading_time'])
                    ? $front['reading_time'] . ' min'
                    : '',
                'date' => $this->formatDate($front['date'] ?? null),
                'authors' => $front['authors'] ?? [],
                'deep_dive_slug' => $front['deep_dive_slug'] ?? null,
                'essay_slug' => $front['essay_slug'] ?? null,
            ]);
        }

        usort($pieces, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $pieces;
    }

    private function formatDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_int($value)) {
            return date('Y-m-d', $value);
        }
        return (string) ($value ?? '');
    }

    /**
     * Split a markdown document into [frontmatter array, body].
     * Returns [[], $content] if no frontmatter is present.
     */
    private function splitFrontmatter(string $content): array
    {
        if (! str_starts_with($content, "---\n")) {
            return [[], $content];
        }

        $end = strpos($content, "\n---\n", 4);
        if ($end === false) {
            return [[], $content];
        }

        $yaml = substr($content, 4, $end - 4);
        $body = substr($content, $end + 5);

        try {
            $front = Yaml::parse($yaml) ?? [];
        } catch (\Throwable) {
            return [[], $content];
        }

        return [$front, $body];
    }
}
