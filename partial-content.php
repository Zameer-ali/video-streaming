public function getVideo($id)
{
    $video = Video::find($id);

    if (!$video) {
        return response()->json([
            'error' => 'Video not found'
        ], 404);
    }

    $path = storage_path('app/'.$video->path);

    $size = filesize($path);
    $offset = 0;

    $headers = [
        'Content-Type' => 'video/mp4',
        'Content-Length' => $size,
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
    ];

    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        $matches = [];
        preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);

        $offset = intval($matches[1]);

        $headers['Content-Length'] = $size - $offset;
        $headers['Accept-Ranges'] = 'bytes';
        $headers['Content-Range'] = 'bytes ' . $offset . '-' . ($size - 1) . '/' . $size;

        // Set HTTP response status code to 206 Partial Content
        return response()->stream(function () use ($path, $offset) {
            $stream = fopen($path, 'rb');
            fseek($stream, $offset);

            while (!feof($stream)) {
                echo fread($stream, 8192);
                ob_flush();
                flush();
            }

            fclose($stream);
        }, 206, $headers);
    }

    // Set HTTP response status code to 200 OK
    return response()->stream(function () use ($path) {
        readfile($path);
    }, 200, $headers);
}
