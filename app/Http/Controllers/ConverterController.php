<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;

class ConverterController extends Controller
{
    public function convertAndStoreVideo()
    {
        // URL of the video to convert
        $originalVideoURL = "https://rr5---sn-2uja-aixd.googlevideo.com/videoplayback?expire=1709063942&ei=purdZb72De6Kp-oP4-6s6A0&ip=182.181.11.243&id=o-AMwc4YJYMF_iRQYUNcHCgXyqCpa4gwczIfWgOPiJede0&itag=18&source=youtube&requiressl=yes&xpc=EgVo2aDSNQ%3D%3D&mh=yT&mm=31%2C29&mn=sn-2uja-aixd%2Csn-hju7en7k&ms=au%2Crdu&mv=m&mvi=5&pl=25&initcwndbps=390000&spc=UWF9f5sR8H2RoD4MJn1jgA7cal-2YJM-s0v6KyUu6YUtlBI&vprv=1&svpuc=1&mime=video%2Fmp4&gir=yes&clen=6449298&ratebypass=yes&dur=109.900&lmt=1708875460973522&mt=1709041502&fvip=2&fexp=24007246&c=ANDROID&txp=6209224&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cxpc%2Cspc%2Cvprv%2Csvpuc%2Cmime%2Cgir%2Cclen%2Cratebypass%2Cdur%2Clmt&sig=AJfQdSswRAIgOYlAKqd4uU7JRqTQhBrF2vpAqmykUFa2RowNOaav7eUCIEnlKS8GgpZRRbo2M_n6r9uUBklZbQuv6CjJeKLkQfdr&lsparams=mh%2Cmm%2Cmn%2Cms%2Cmv%2Cmvi%2Cpl%2Cinitcwndbps&lsig=APTiJQcwRgIhAPTN4A5AnnThVPbHuphxgD09WQG2wRj05kHxehwWPCiPAiEAgrQzfhLcfV_j_nDqTR5nzSOU7DyYNOs2j3mqTTx8TPU%3D";
        // Temporary directory to store the converted video
        $tempDir = storage_path('app/public/temp');

        // Create the directory if it doesn't exist
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Generate a unique filename for the converted video
        $convertedFilename = 'converted_' . time() . '.mp4';

        // Full path to the converted video
        $convertedFilePath = $tempDir . '/' . $convertedFilename;

        // Initialize FFMpeg
        $ffmpeg = FFMpeg::create();

        // Open the original video file
        $video = $ffmpeg->open($originalVideoURL);

        // Convert the video to custom resolution (e.g., 1280x720)
        $format = new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
        $format->setKiloBitrate(1200); // Adjust the bitrate as needed
        $format->setAudioCodec('libmp3lame');
        $format->setAudioChannels(2);
        $format->setAudioKiloBitrate(256);

        $video->filters()->resize(new \FFMpeg\Coordinate\Dimension(1280, 720))->synchronize();
        $video->save($format, $convertedFilePath);

        // Assuming the file was successfully converted, store it
        if (file_exists($convertedFilePath)) {
            // Store the file in the storage/app/public/videos directory
            $newFilePath = 'videos/' . $convertedFilename;
            Storage::disk('public')->put($newFilePath, file_get_contents($convertedFilePath));

            // Return the new URL
            $newVideoURL = Storage::disk('public')->url($newFilePath);
            return $newVideoURL;
        }

        // Return the original URL if conversion fails
        return $originalVideoURL;
    }
}
