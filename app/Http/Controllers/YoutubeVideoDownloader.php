<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use YouTube\YouTubeDownloader;
use YouTube\Exception\YouTubeException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class YoutubeVideoDownloader extends Controller
{
    function index()
    {
        // https://youtu.be/xkhUH9Fx2z8?si=elR8OZZyYeklr-Oq
        $youtube = new YouTubeDownloader();
        // $downloadOptios = $youtube->getDownloadLinks("https://youtu.be/Yuhnsy4O3lc?si=ww_EolfvPW6jpNF8");
        // $firstFormat = $downloadOptions->getAudioFormats()[2];
        // $title = $downloadOptions->getInfo()->title;
        // $url = $firstFormat->url;

        // $title = str_replace(' ', '_', $title);

        // $downloadableUrl = ['title' => $title, 'url' => $url.'&title='.$title];
        // return response()->json($downloadableUrl);
        
        // dd(
        //     [
        //         // $downloadOptions->getAudioFormats()[6], 
        //         // $downloadOptions->getVideoFormats()[3],
        //         // $downloadOptions->getFirstCombinedFormat(),
        //         // $downloadOptions->getCombinedFormats(),
        //         // $downloadOptions->getInfo()->title,
        //         // $youtube->getThumbnails("aqz-KE-bpKQ"),
        //         // $title
        //         // $downloadOptions,
        //         // $youtube->getPage("https://youtu.be/xkhUH9Fx2z8?si=elR8OZZyYeklr-Oq"),
        //         $downloadOptions->getAllFormats()
        //     ]
        // );

        try {
            $downloadOptions = (new YouTubeDownloader())->getDownloadLinks("https://youtu.be/Yuhnsy4O3lc?si=ww_EolfvPW6jpNF8");
            // dd([
            //     $downloadOptions,
            //     $downloadOptions->getAudioFormats(), 
            //     $downloadOptions->getVideoWithAudioFormats(),
            //     $downloadOptions->getVideoWithOutAudioFormats(),

            // ]);
            if (!$downloadOptions->getAllFormats()) {
                throw new YouTubeException('No links found');
            }

            $videoUrl = $downloadOptions->getFirstCombinedFormat()->url;
            $videoContents = $this->fetchVideoContents($videoUrl);

            $filename = $this->generateFileName('mp4');
            $filePath = $this->saveVideoToStorage($filename, $videoContents);

            $url = $this->generatePublicUrl($filePath);

            return view('youtube_download', compact('url'));
        } catch (YouTubeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function getAllFormats(Request $response){
        try {
            $youtube = new YouTubeDownloader();
            $downloadOptions = $youtube->getDownloadLinks($response->videoLink);
            $title = $downloadOptions->getInfo()->title;
            $thumbnail = $youtube->getThumbnails($downloadOptions->getInfo()->id)['high'];
            $getAudioFormats = $downloadOptions->getAudioFormats(); 
            $getVideoWithAudioFormats = $downloadOptions->getVideoWithAudioFormats();
            $getVideoWithOutAudioFormats = $downloadOptions->getVideoWithOutAudioFormats();
            return response()->json([
                'success' => true, 
                'title' => $title,
                'thumbnail' => $thumbnail,
                'autoFormat' => $downloadOptions->getFirstCombinedFormat(),
                'audioFormats' => $getAudioFormats,
                'videoWithAudioFormats' => $getVideoWithAudioFormats,
                'videoWithOutAudioFormats' => $getVideoWithOutAudioFormats,
            ]);

        } catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }

    }

    private function fetchVideoContents($videoUrl)
    {
        $client = new Client();
        $response = $client->get($videoUrl);
        return $response->getBody()->getContents();
    }

    private function generateFileName($extension)
    {
        return 'video_' . time() . '.' . $extension;
    }

    private function saveVideoToStorage($filename, $videoContents)
    {
        $filePath = 'videos/' . $filename;
        Storage::disk('public')->put($filePath, $videoContents);
        return $filePath;
    }

    private function generatePublicUrl($filePath)
    {
        return url(Storage::url($filePath));
    }

}
