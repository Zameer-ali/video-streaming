public function getVideo($id)
{
    $video = Video::find($id);

    if (!$video) {
        abort(404);
    }

    $path = storage_path('app/'.$video->path);

    return response()->stream(function () use ($path) {
        $stream = fopen($path, 'rb');
        fpassthru($stream);
        fclose($stream);
    }, 200, [
        'Content-Type' => 'video/mp4',
        'Content-Length' => filesize($path),
        'Accept-Ranges' => 'bytes',
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
    ]);
}
