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
    $length = $size;

    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        $matches = [];
        preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);

        $offset = intval($matches[1]);
        $length = intval($matches[2]) - $offset + 1;

        $headers = [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $size,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ];

        return response()->stream(function () use ($path, $offset, $length) {
            $stream = fopen($path, 'rb');
            fseek($stream, $offset);
            echo fread($stream, $length);
            fclose($stream);
        }, 206, $headers);
    } else {
        $headers = [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $size,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ];

        return response()->stream(function () use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, $headers);
    }
}
