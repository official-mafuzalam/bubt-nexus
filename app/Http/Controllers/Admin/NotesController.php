<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NotesController extends Controller
{
    // Show listing + upload form (Inertia page)
    public function index(Request $request)
    {
        $notes = Note::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'file_type' => $n->file_type,
                'file_url' => $n->file_url,
                'created_at' => $n->created_at->toDateTimeString(),
            ]);

        return Inertia::render('admin/Notes/Index', [
            'notes' => $notes,
        ]);
    }

    // Store uploaded file to Firebase bucket
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpeg,png,jpg,gif,webp',
            'course_id' => 'nullable|integer'
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getClientMimeType();

        $random = Str::random(20);
        $filename = time() . '_' . $random . '.' . $ext;

        // folder by course/year
        $folder = 'notes/' . ($request->course_id ?? 'general') . '/' . now()->year;
        $objectName = $folder . '/' . $filename;

        // Upload using Google Cloud Storage client
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        $storage = new StorageClient([
            'keyFilePath' => $credentialsPath,
        ]);

        $bucketName = env('FIREBASE_STORAGE_BUCKET');
        $bucket = $storage->bucket($bucketName);

        // read the file stream
        $stream = fopen($file->getRealPath(), 'r');

        // upload and make public
        $object = $bucket->upload($stream, [
            'name' => $objectName,
            'predefinedAcl' => 'publicRead', // makes the object publicly readable
        ]);

        // create public URL
        $fileUrl = "https://storage.googleapis.com/{$bucketName}/" . rawurlencode($objectName);

        // Save DB record
        $note = Note::create([
            'user_id' => auth()->id(),
            'course_id' => $request->course_id,
            'title' => $request->title,
            'file_url' => $fileUrl,
            'storage_path' => $objectName,
            'file_type' => $ext === 'pdf' ? 'pdf' : 'image',
            'file_size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Uploaded successfully!');
    }

    // Delete a note: remove from bucket and DB
    public function destroy(Note $note)
    {
        // authorize: only owner (adjust as needed)
        $this->authorize('delete', $note);

        $credentialsPath = env('FIREBASE_CREDENTIALS');
        $storage = new StorageClient([
            'keyFilePath' => $credentialsPath,
        ]);
        $bucket = $storage->bucket(env('FIREBASE_STORAGE_BUCKET'));

        // delete object if exists
        if ($note->storage_path) {
            $object = $bucket->object($note->storage_path);
            if ($object->exists()) {
                $object->delete();
            }
        }

        $note->delete();

        return redirect()->back()->with('success', 'Deleted successfully.');
    }
}
