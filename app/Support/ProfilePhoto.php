<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentPhoto;
use Illuminate\Http\UploadedFile;

/**
 * Turns an uploaded picture into a small square JPEG and saves it in the database.
 */
class ProfilePhoto
{
    private const SIZE = 400;             // output is SIZE x SIZE pixels
    private const MAX_PIXELS = 12_000_000; // skip resizing huge images (GD would run out of memory)

    /** Save (or replace) the student's picture and mark the student as having one. */
    public static function store(Student $student, UploadedFile $file): void
    {
        [$mime, $bytes] = self::prepare($file);

        StudentPhoto::updateOrCreate(
            ['student_id' => $student->id],
            ['mime' => $mime, 'data' => base64_encode($bytes)]
        );

        if ($student->profile_photo !== Student::PHOTO_IN_DB) {
            $student->update(['profile_photo' => Student::PHOTO_IN_DB]);
        }
    }

    /** @return array{0: string, 1: string} [mime type, raw image bytes] */
    private static function prepare(UploadedFile $file): array
    {
        $bytes = (string) file_get_contents($file->getRealPath());
        $original = [$file->getMimeType() ?: 'image/jpeg', $bytes];

        // No GD extension on this server: store the upload as-is.
        if (! function_exists('imagecreatefromstring')) {
            return $original;
        }

        $info = @getimagesizefromstring($bytes);
        if ($info === false || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            return $original;
        }

        $source = @imagecreatefromstring($bytes);
        if ($source === false) {
            return $original;
        }

        // Centre-crop to a square, then shrink to SIZE (never enlarge).
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $out = min(self::SIZE, $side);

        $canvas = imagecreatetruecolor($out, $out);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // white behind transparent PNGs
        imagecopyresampled(
            $canvas, $source,
            0, 0,
            intdiv($width - $side, 2), intdiv($height - $side, 2),
            $out, $out,
            $side, $side
        );

        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = (string) ob_get_clean();

        return ['image/jpeg', $jpeg];
    }
}
