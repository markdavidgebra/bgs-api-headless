<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogsController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::query()
            ->latest('published_at')
            ->latest('id')
            ->paginate(15);

        return view('admin.pages.blogs.index', compact('blogs'));
    }

    public function create(): View
    {
        return view('admin.pages.blogs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $slug = $this->resolveUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title'],
        );

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storePublicBlogImage($request->file('image'));
        }

        Blog::query()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'author_name' => $validated['author_name'] ?? 'Admin',
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? ($validated['status'] === 'published' ? now() : null),
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.blogs')->with('status', __('Blog created.'));
    }

    public function show(int|string $id): View
    {
        $blog = Blog::query()->findOrFail($id);

        return view('admin.pages.blogs.show', compact('blog'));
    }

    public function edit(int|string $id): View
    {
        $blog = Blog::query()->findOrFail($id);

        return view('admin.pages.blogs.edit', compact('blog'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $blog = Blog::query()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'slug' => $this->resolveUniqueSlug(
                $validated['slug'] ?? null,
                $validated['title'],
                (int) $blog->id,
            ),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'author_name' => $validated['author_name'] ?? 'Admin',
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? ($validated['status'] === 'published' ? ($blog->published_at ?? now()) : null),
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredBlogImage($blog->image);
            $payload['image'] = $this->storePublicBlogImage($request->file('image'));
        }

        $blog->update($payload);

        return redirect()->route('admin.blogs')->with('status', __('Blog updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $blog = Blog::query()->findOrFail($id);
        $this->deleteStoredBlogImage($blog->image);
        $blog->delete();

        return redirect()->route('admin.blogs')->with('status', __('Blog deleted.'));
    }

    private function storePublicBlogImage(UploadedFile $file): string
    {
        $dir = public_path('blogs');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'blogs/'.$name;
    }

    private function deleteStoredBlogImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        // Keep shared static template assets in place.
        if (Str::startsWith($path, 'frontend/')) {
            return;
        }

        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }
    }

    private function resolveUniqueSlug(?string $rawSlug, string $title, ?int $ignoreId = null): string
    {
        $slug = filled($rawSlug) ? Str::slug($rawSlug) : Str::slug($title);

        if ($slug === '') {
            $slug = 'blog-'.Str::lower(Str::random(8));
        }

        $baseSlug = $slug;
        $i = 1;
        $query = Blog::query();
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        while ((clone $query)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        return $slug;
    }
}
