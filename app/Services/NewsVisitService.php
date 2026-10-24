<?php

namespace App\Services;

use App\Models\News;
use App\Models\PageVisit;
use Illuminate\Http\Request;

class NewsVisitService
{
    /**
     * Catat kunjungan ke halaman berita dengan hashing pengunjung untuk mencegah duplikasi dalam 1 hari.
     */
    public function recordVisit(News $news, Request $request): PageVisit
    {
        $rawIdentifier = ($request->ip() ?? 'unknown') . '|' . ($request->userAgent() ?? 'none');
        $visitorHash = hash('sha256', $rawIdentifier);
        $today = now()->startOfDay();

        // Cek apakah pengunjung dengan hash yang sama sudah tercatat hari ini untuk berita ini
        $existingVisit = PageVisit::where('news_id', $news->news_id)
            ->where('visitor_hash', $visitorHash)
            ->where('visited_at', '>=', $today)
            ->first();

        if ($existingVisit) {
            return $existingVisit;
        }

        return PageVisit::create([
            'news_id' => $news->news_id,
            'page_path' => $request->path(),
            'visitor_hash' => $visitorHash,
            'visited_at' => now(),
        ]);
    }
}
