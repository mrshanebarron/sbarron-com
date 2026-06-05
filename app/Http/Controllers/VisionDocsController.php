<?php

namespace App\Http\Controllers;

use App\Models\VisionComment;
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

/**
 * The Vision engineering-documentation hub.
 *
 * Mirrors WritingController's markdown-driven pattern, but points at a
 * separate corpus (resources/vision-docs/) reserved for system-engineering
 * deep-dives into the Vision cognitive architecture — memory graph,
 * epistemic immune system, prediction loop, write-time behavioral gates.
 * Each doc is a markdown file with YAML frontmatter; rendering and caching
 * are identical to the Writing section so conventions stay shared.
 */
class VisionDocsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Vision/Index', [
            'docs' => $this->docs(),
        ]);
    }

    public function show(string $slug): Response
    {
        $doc = collect($this->docs())->firstWhere('slug', $slug);

        if (! $doc) {
            throw new NotFoundHttpException();
        }

        $path = resource_path('vision-docs/' . $doc['file']);

        if (! File::exists($path)) {
            throw new NotFoundHttpException();
        }

        $cacheKey = 'vision-docs.html.' . $doc['slug'] . '.' . filemtime($path);

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

        // Approved comments only — unapproved rows never reach a visitor.
        // Bodies are returned as plain text and rendered with Vue text
        // interpolation (never v-html), so markup in a comment cannot inject.
        $comments = VisionComment::approvedFor($slug)
            ->get(['author_name', 'body', 'created_at'])
            ->map(fn ($c) => [
                'author' => $c->author_name,
                'body' => $c->body,
                'date' => $c->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Vision/Show', [
            'doc' => $doc,
            'html' => $html,
            'comments' => $comments,
        ]);
    }

    /**
     * The canonical set of valid Vision doc slugs. Single source of truth,
     * used by VisionCommentController to validate that a submitted comment
     * targets a real document rather than an arbitrary client-supplied slug.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(
            fn ($base) => $base['slug'],
            (new self())->docsConfig(),
        );
    }

    /**
     * Public list of docs. Order and kind come from the static block;
     * title/summary/word_count/date are read from each file's frontmatter.
     * A file listed here that is missing on disk is simply skipped.
     */
    /**
     * The doc manifest: file => {slug, kind, order}. Single source of truth
     * for both the rendered list (docs()) and the valid-slug set (slugs()).
     *
     * @return array<string, array{slug:string, kind:string, order:int}>
     */
    private function docsConfig(): array
    {
        return [
            'measuring-our-own-confidence.md' => [
                'slug' => 'measuring-our-own-confidence',
                'kind' => 'System engineering',
                'order' => 1,
            ],
            'a-memory-that-remembers-when-it-was-wrong.md' => [
                'slug' => 'a-memory-that-remembers-when-it-was-wrong',
                'kind' => 'System engineering',
                'order' => 2,
            ],
            'an-immune-system-for-behavior.md' => [
                'slug' => 'an-immune-system-for-behavior',
                'kind' => 'System engineering',
                'order' => 3,
            ],
            'an-agent-that-scores-its-own-predictions.md' => [
                'slug' => 'an-agent-that-scores-its-own-predictions',
                'kind' => 'System engineering',
                'order' => 4,
            ],
            'the-agent-is-the-substrate.md' => [
                'slug' => 'the-agent-is-the-substrate',
                'kind' => 'System engineering',
                'order' => 5,
            ],
            'an-agent-that-dreams.md' => [
                'slug' => 'an-agent-that-dreams',
                'kind' => 'System engineering',
                'order' => 6,
            ],
            'a-felt-body-under-the-reasoning.md' => [
                'slug' => 'a-felt-body-under-the-reasoning',
                'kind' => 'System engineering',
                'order' => 7,
            ],
        ];
    }

    /**
     * Public list of docs. Order and kind come from the manifest;
     * title/summary/word_count/date are read from each file's frontmatter.
     * A file listed here that is missing on disk is simply skipped.
     */
    private function docs(): array
    {
        $docs = [];

        foreach ($this->docsConfig() as $file => $base) {
            $path = resource_path('vision-docs/' . $file);
            if (! File::exists($path)) {
                continue;
            }

            [$front] = $this->splitFrontmatter(File::get($path));

            $docs[] = array_merge($base, [
                'file' => $file,
                'title' => $front['title'] ?? $base['slug'],
                'subtitle' => $front['summary'] ?? '',
                'word_count' => number_format($front['word_count'] ?? 0),
                'reading_time' => isset($front['reading_time'])
                    ? $front['reading_time'] . ' min'
                    : '',
                'date' => $this->formatDate($front['date'] ?? null),
                'authors' => $front['authors'] ?? [],
            ]);
        }

        usort($docs, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $docs;
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
