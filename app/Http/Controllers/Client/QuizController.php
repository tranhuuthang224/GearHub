<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\QuizLog;
use App\Services\GearMatchEngine;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(
        protected GearMatchEngine $engine
    ) {}

    /**
     * Màn hình giao diện Quiz
     */
    public function index()
    {
        return view('client.quiz.index');
    }

    /**
     * Nhận câu trả lời, tính toán Top Matches và lưu Log
     * POST /quiz/submit
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'purpose'     => 'nullable|string',
            'budget'      => 'nullable|string',
            'device_type' => 'nullable|string',
            'features'    => 'nullable|array',
        ]);

        // Chạy thuật toán chấm điểm
        $topMatches = $this->engine
            ->forAnswers($validated)
            ->getTopMatches(3);

        // Lưu log để phân tích thị hiếu
        QuizLog::create([
            'user_id'                 => auth()->id(),
            'session_id'              => session()->getId(),
            'ip_address'              => $request->ip(),
            'answers'                 => $validated,
            'recommended_product_ids' => $topMatches->pluck('id')->toArray(),
        ]);

        // Trả kết quả JSON cho Alpine.js của Member A render màn hình kết quả
        return response()->json([
            'success' => true,
            'matches' => $topMatches,
        ]);
    }
}