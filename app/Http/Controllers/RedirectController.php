<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

class RedirectController extends Controller
{
    public function redirect(Request $request, $code)
    {
        $link = Link::where('short_code', $code)->orWhere('alias', $code)->firstOrFail();

        $dd = new DeviceDetector($request->userAgent());
        $dd->parse();

        $ipAddress = $this->getIp($request);
        $location = '';
        try {
            $response = Http::get("http://ip-api.com/json/{$ipAddress}");
            Log::info('IP API Response: ', $response->json());
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success') {
                    $location = "{$data['city']}, {$data['regionName']}, {$data['country']}";
                }
            }
        } catch (\Exception $e) {
            Log::error('IP API Error: ' . $e->getMessage());
        }

        $browser_info = [
            'ip_address' => $ipAddress,
            'browser' => $dd->getClient('name'),
            'os' => $dd->getOs('name'),
            'location' => $location,
        ];
        $link->clicks()->create($browser_info);
        $link->increment('visits');

        list($appUrl, $serviceName) = $this->getAppUrl($link->original_url);
    
        $link_info = [
            'appUrl' => $appUrl,
            'webUrl' => $link->original_url,
            'serviceName' => $serviceName,
        ];
        Log::info('URL Click: ' . json_encode(['link' => $link_info, 'browser' => $browser_info]));
        return view('redirect', array_merge($link_info, ['link' => $link]));
    }

    private function getIp(Request $request)
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return $request->ip(); // Fallback
    }

    private function getAppUrl($url)
    {
        // youtube shorts
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return ['vnd.youtube://shorts/' . $matches[1], 'YouTube'];
        }

        // youtube playlist
        if (preg_match('/(youtube\.com|youtu\.be)\/(playlist\?list=)?([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return ['vnd.youtube://' . $matches[0], 'YouTube'];
        }
        
        // youtube videos
        if (preg_match('/(youtube\.com|youtu\.be)\/(watch\?v=)?([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return ['vnd.youtube:' . $matches[3], 'YouTube'];
        }

        // youtube general
        if (preg_match('/youtube\.com\/([^\/]+)/', $url, $matches)) {
            return ['vnd.youtube://youtube.com/' . $matches[1], 'YouTube'];
        }

        // instagram posts
        if (preg_match('/instagram\.com\/p\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return ['instagram://media?id=' . $matches[1], 'Instagram'];
        }

        return [$url, 'the website'];
    }
}
