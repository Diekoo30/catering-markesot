<?php

namespace App\Http\Controllers;

use App\Services\AHPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * RecommendationController
 *
 * Endpoint: POST /api/recommendation
 *
 * Payload JSON dari markesot.js:
 * {
 *   "rasa_vs_nutrisi"  : "kiri" | "kanan" | "sama",
 *   "rasa_vs_jenis"    : "kiri" | "kanan" | "sama",
 *   "nutrisi_vs_jenis" : "kiri" | "kanan" | "sama"
 * }
 *
 * Response JSON (selalu HTTP 200, tidak ada CR blocking):
 * {
 *   "success" : true,
 *   "weights" : { "rasa":0.57, "nutrisi":0.29, "jenis_hidangan":0.14 },
 *   "consistency" : { "cr":0.118, "lambda_max":3.137, ... },
 *   "ranked" : [
 *     { "nama_menu":"Nasi Rawon", "final_score":2.72, "match_percentage":100, ... },
 *     ...
 *   ]
 * }
 */
class RecommendationController extends Controller
{
    public function __construct(
        private readonly AHPService $ahpService
    ) {}

    /**
     * Proses 3 pilihan VS dari kuesioner frontend → matriks AHP 3×3 → ranking menu.
     *
     * ALUR DINAMIS:
     *   1. Ambil 3 input dari request POST (rasa_vs_nutrisi, rasa_vs_jenis, nutrisi_vs_jenis)
     *   2. Validasi: harus 'kiri', 'kanan', atau 'sama'
     *   3. Kirim ke AHPService->getRankedMenusFromChoices() sebagai parameter
     *   4. AHPService membangun matriks 3x3, menghitung bobot, meranking menu
     *   5. Return JSON ke frontend
     */
    public function getRecommendation(Request $request): JsonResponse
    {
        // ── 1. Ambil input DINAMIS dari request POST frontend ──────
        $rasa_vs_nutrisi  = $request->input('rasa_vs_nutrisi');
        $rasa_vs_jenis    = $request->input('rasa_vs_jenis');
        $nutrisi_vs_jenis = $request->input('nutrisi_vs_jenis');

        // ── 2. Validasi: ketiga field wajib dan hanya boleh kiri/kanan/sama ──
        $validator = Validator::make($request->all(), [
            'rasa_vs_nutrisi'  => ['required', 'string', 'in:kiri,kanan,sama'],
            'rasa_vs_jenis'    => ['required', 'string', 'in:kiri,kanan,sama'],
            'nutrisi_vs_jenis' => ['required', 'string', 'in:kiri,kanan,sama'],
        ], [
            'required' => 'Field :attribute wajib diisi.',
            'in'       => 'Nilai :attribute harus: kiri, kanan, atau sama.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'error'   => 'Input tidak valid.',
                'errors'  => $validator->errors(),
                'ranked'  => [],
            ], 422);
        }

        // ── 3. Kirim pilihan user KE AHPService sebagai parameter dinamis ──
        $choices = [
            'rasa_vs_nutrisi'  => $rasa_vs_nutrisi,
            'rasa_vs_jenis'    => $rasa_vs_jenis,
            'nutrisi_vs_jenis' => $nutrisi_vs_jenis,
        ];

        // ── 4. AHPService memproses: matriks → bobot → ranking ──────
        $result = $this->ahpService->getRankedMenusFromChoices($choices);

        // ── 5. Bangun response JSON yang kompatibel dengan markesot.js ──
        $rawData = $result['data'] ? $result['data']->toArray() : [];

        $ranked = array_values(array_map(function (array $item): array {
            return array_merge($item, [
                'score'      => $item['final_score'],
                'percentage' => $item['match_percentage'] . '%',
            ]);
        }, $rawData));

        $cr = $result['consistency']['cr'] ?? 0;

        return response()->json([
            'status'      => 'success',
            'success'     => true,
            'cr'          => $cr,
            'weights'     => $result['weights'],
            'consistency' => $result['consistency'],
            'ranked'      => $ranked,
        ], 200);
    }
}
