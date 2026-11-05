<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerFileRequest;
use App\Models\OwnerFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', OwnerFile::class);

        $query = OwnerFile::query()
            ->with('uploader:id,name')
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->where('category', $category);
        }

        $files = $query->paginate(20)->withQueryString();

        return view('owner.files.index', [
            'files' => $files,
            'filters' => $request->only(['search', 'category']),
            'categories' => OwnerFile::CATEGORIES,
            'stats' => [
                'total' => OwnerFile::count(),
                'documents' => OwnerFile::where('category', 'document')->count(),
                'images' => OwnerFile::where('category', 'image')->count(),
                'archives' => OwnerFile::where('category', 'archive')->count(),
                'storage_bytes' => (int) OwnerFile::sum('size_bytes'),
            ],
        ]);
    }

    public function store(StoreOwnerFileRequest $request): RedirectResponse
    {
        $upload = $request->file('file');
        $extension = strtolower($upload->getClientOriginalExtension() ?: $upload->extension() ?: '');
        $mime = $upload->getClientMimeType() ?: $upload->getMimeType();
        $category = $request->input('category') ?: OwnerFile::detectCategory($mime, $extension);

        $path = $upload->store('owner/files/'.now()->format('Y/m'), 'local');

        OwnerFile::create([
            'title' => $request->string('title')->trim()->toString(),
            'description' => $request->string('description')->trim()->toString() ?: null,
            'original_name' => $upload->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime_type' => $mime,
            'extension' => $extension ?: null,
            'size_bytes' => $upload->getSize() ?: 0,
            'category' => in_array($category, OwnerFile::CATEGORIES, true) ? $category : 'other',
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('owner.files.index')
            ->with('success', __('owner.files.uploaded'));
    }

    public function download(OwnerFile $file): StreamedResponse
    {
        $this->authorize('download', $file);

        abort_unless($file->existsOnDisk(), 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function destroy(OwnerFile $file): RedirectResponse
    {
        $this->authorize('delete', $file);

        if ($file->existsOnDisk()) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $file->delete();

        return redirect()
            ->route('owner.files.index')
            ->with('success', __('owner.files.deleted'));
    }
}
