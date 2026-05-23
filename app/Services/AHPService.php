<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Log;

/**
 * AHPService — Analytical Hierarchy Process (3×3)
 *
 * Referensi: Saaty (1980), Ricky Isnawan UKDW (2021).
 *
 * KRITERIA (Indeks 0–2):
 *   [0] Rasa           — 2.0=Segar | 2.2=Agak Segar | 3.0=Pedas
 *   [1] Nutrisi        — 1.2=Karbo | 2.0=Telur | 2.6=Ayam | 3.0=Sapi
 *   [2] Jenis Hidangan — 2.5=Kuah Ringan | 2.8=Kering/Kuah | 3.0=Mutlak
 *
 * 3 PASANG PERTANYAAN VS (C(3,2) = 3):
 *   rasa_vs_nutrisi  → M[0][1] & M[1][0]
 *   rasa_vs_jenis    → M[0][2] & M[2][0]
 *   nutrisi_vs_jenis → M[1][2] & M[2][1]
 *
 * SKALA SAATY KONSTAN = 3:
 *   'sama'  → nilai 1     (Sama Penting)
 *   'kiri'  → kiri = 3,   kanan = 1/3
 *   'kanan' → kanan = 3,  kiri  = 1/3
 */
class AHPService
{
    /** Jumlah kriteria */
    private const N  = 3;

    /** Random Index untuk n=3 (Saaty, 1980) */
    private const RI = 0.58;

    /** Skala Saaty konstan: kriteria yang menang mendapat nilai 3 */
    private const SCALE = 3;

    // ──────────────────────────────────────────────────────────────
    // ENTRY POINT
    // ──────────────────────────────────────────────────────────────

    /**
     * Terima 3 pilihan user dari Controller → bangun matriks → ranking.
     *
     * @param  array  $choices  Contoh:
     *   ['rasa_vs_nutrisi'=>'kiri', 'rasa_vs_jenis'=>'sama', 'nutrisi_vs_jenis'=>'kanan']
     */
    public function getRankedMenusFromChoices(array $choices): array
    {
        // ── LOG: Catat input yang DITERIMA dari Controller ──
        Log::info('[AHP] Input diterima dari Controller:', $choices);

        // Langkah 1: Bangun matriks 3x3 dari pilihan user (DINAMIS)
        $matrix = $this->buildMatrixFromChoices($choices);

        // ── LOG: Catat matriks yang DIBANGUN ──
        Log::info('[AHP] Matriks 3x3 yang dibangun:', $matrix);

        // Langkah 2: Hitung bobot, CR, dan ranking
        return $this->getRankedMenus($matrix);
    }

    // ──────────────────────────────────────────────────────────────
    // MATRIX BUILDER
    // ──────────────────────────────────────────────────────────────

    /**
     * Membangun Pairwise Comparison Matrix 3×3 dari pilihan user.
     *
     * ATURAN SKALA KONSTAN 3:
     *
     *   'kiri'  → M[i][j] = 3.0     M[j][i] = 1/3 = 0.333...
     *   'kanan' → M[i][j] = 1/3     M[j][i] = 3.0
     *   'sama'  → M[i][j] = 1.0     M[j][i] = 1.0
     *
     * Contoh konkret untuk 'rasa_vs_nutrisi':
     *   'kiri'  (Rasa penting)   → matriks[0][1] = 3.0,   matriks[1][0] = 0.333
     *   'kanan' (Nutrisi penting)→ matriks[0][1] = 0.333, matriks[1][0] = 3.0
     *   'sama'                   → matriks[0][1] = 1.0,   matriks[1][0] = 1.0
     */
    public function buildMatrixFromChoices(array $choices): array
    {
        // Inisialisasi matriks identitas 3×3
        $M = [
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
        ];

        // ──────────────────────────────────────────────
        // PASANG 1: rasa_vs_nutrisi → M[0][1] & M[1][0]
        // ──────────────────────────────────────────────
        $p1 = $choices['rasa_vs_nutrisi'] ?? 'sama';
        if ($p1 === 'kiri') {
            // Rasa lebih penting dari Nutrisi
            $M[0][1] = 3.0;          // Rasa → Nutrisi = 3
            $M[1][0] = 1.0 / 3.0;    // Nutrisi → Rasa = 1/3
        } elseif ($p1 === 'kanan') {
            // Nutrisi lebih penting dari Rasa
            $M[0][1] = 1.0 / 3.0;    // Rasa → Nutrisi = 1/3
            $M[1][0] = 3.0;          // Nutrisi → Rasa = 3
        }
        // 'sama' → tetap 1.0 (sudah dari inisialisasi)

        // ──────────────────────────────────────────────
        // PASANG 2: rasa_vs_jenis → M[0][2] & M[2][0]
        // ──────────────────────────────────────────────
        $p2 = $choices['rasa_vs_jenis'] ?? 'sama';
        if ($p2 === 'kiri') {
            // Rasa lebih penting dari Jenis Hidangan
            $M[0][2] = 3.0;          // Rasa → Jenis = 3
            $M[2][0] = 1.0 / 3.0;    // Jenis → Rasa = 1/3
        } elseif ($p2 === 'kanan') {
            // Jenis Hidangan lebih penting dari Rasa
            $M[0][2] = 1.0 / 3.0;    // Rasa → Jenis = 1/3
            $M[2][0] = 3.0;          // Jenis → Rasa = 3
        }

        // ──────────────────────────────────────────────
        // PASANG 3: nutrisi_vs_jenis → M[1][2] & M[2][1]
        // ──────────────────────────────────────────────
        $p3 = $choices['nutrisi_vs_jenis'] ?? 'sama';
        if ($p3 === 'kiri') {
            // Nutrisi lebih penting dari Jenis Hidangan
            $M[1][2] = 3.0;          // Nutrisi → Jenis = 3
            $M[2][1] = 1.0 / 3.0;    // Jenis → Nutrisi = 1/3
        } elseif ($p3 === 'kanan') {
            // Jenis Hidangan lebih penting dari Nutrisi
            $M[1][2] = 1.0 / 3.0;    // Nutrisi → Jenis = 1/3
            $M[2][1] = 3.0;          // Jenis → Nutrisi = 3
        }

        // ── LOG: Tampilkan setiap nilai yang di-set ──
        Log::info("[AHP] Pasang 1: rasa_vs_nutrisi = '{$p1}' → M[0][1]={$M[0][1]}, M[1][0]={$M[1][0]}");
        Log::info("[AHP] Pasang 2: rasa_vs_jenis   = '{$p2}' → M[0][2]={$M[0][2]}, M[2][0]={$M[2][0]}");
        Log::info("[AHP] Pasang 3: nutrisi_vs_jenis = '{$p3}' → M[1][2]={$M[1][2]}, M[2][1]={$M[2][1]}");

        return $M;
    }

    // ──────────────────────────────────────────────────────────────
    // CORE ENGINE
    // ──────────────────────────────────────────────────────────────

    /**
     * Hitung Priority Vector, CR, dan ranking semua menu.
     *
     * finalScore = (bobot_rasa × skor_rasa) + (bobot_nutrisi × skor_nutrisi)
     *            + (bobot_jenis × skor_jenis_hidangan)
     */
    public function getRankedMenus(array $matrix): array
    {
        // ── 1. Bobot kriteria (Priority Vector) ──
        $weights = $this->calculatePriorityVector($matrix);

        // ── LOG bobot ──
        Log::info('[AHP] Bobot hasil:', [
            'rasa'  => round($weights[0], 4),
            'nutri' => round($weights[1], 4),
            'jenis' => round($weights[2], 4),
        ]);

        // ── 2. CR untuk laporan ──
        $consistency = $this->calculateConsistencyRatio($matrix, $weights);

        // ── 3. Ambil semua menu dari database (hanya yang tersedia & berkategori AHP aktif) ──
        $menuItems = MenuItem::available()
            ->whereHas('category', function ($query) {
                $query->where('enable_ahp_recommendation', true);
            })->get();

        if ($menuItems->isEmpty()) {
            return [
                'success'     => true,
                'data'        => collect([]),
                'weights'     => $this->formatWeights($weights),
                'consistency' => $consistency,
                'error'       => null,
            ];
        }

        // ── 4. Scoring: finalScore = Σ(bobot[i] × skor[i]) ──
        $ranked = $menuItems->map(function (MenuItem $item) use ($weights) {
            $scores     = $item->getSkorArray();
            $finalScore = 0.0;

            for ($i = 0; $i < self::N; $i++) {
                $finalScore += $weights[$i] * $scores[$i];
            }

            return [
                'id'                  => $item->id,
                'nama_menu'           => $item->name,
                'harga'               => (int) $item->price,
                'harga_format'        => 'Rp ' . number_format((int) $item->price, 0, ',', '.'),
                'image'               => $item->image ? asset('storage/' . $item->image) : null,
                'skor_rasa'           => $item->skor_rasa,
                'skor_nutrisi'        => $item->skor_nutrisi,
                'skor_jenis_hidangan' => $item->skor_jenis_hidangan,
                'final_score'         => round($finalScore, 6),
                'skor_detail'         => [
                    'rasa'           => round($weights[0] * $scores[0], 6),
                    'nutrisi'        => round($weights[1] * $scores[1], 6),
                    'jenis_hidangan' => round($weights[2] * $scores[2], 6),
                ],
            ];
        });

        // ── 5. Urutkan DESC berdasarkan skor akhir, lalu berdasarkan nama menu (DESC) jika skor sama (tie-breaker) ──
        $sorted   = $ranked->sortBy([
            ['final_score', 'desc'],
            ['nama_menu', 'desc'],
        ])->values();
        $maxScore = $sorted->first()['final_score'] ?? 1;

        $result = $sorted->map(function ($item) use ($maxScore) {
            $item['match_percentage'] = $maxScore > 0
                ? round(($item['final_score'] / $maxScore) * 100, 1)
                : 0;
            return $item;
        })->values();

        // ── LOG ranking final ──
        Log::info('[AHP] Ranking #1: ' . ($result->first()['nama_menu'] ?? '-'));

        return [
            'success'     => true,
            'data'        => $result,
            'weights'     => $this->formatWeights($weights),
            'consistency' => $consistency,
            'error'       => null,
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // RUMUS MATEMATIS AHP (Saaty 1980)
    // ──────────────────────────────────────────────────────────────

    /**
     * Priority Vector — rata-rata baris normalisasi.
     */
    public function calculatePriorityVector(array $M): array
    {
        $n = self::N;

        // Jumlah tiap kolom
        $colSum = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $colSum[$j] += $M[$i][$j];
            }
        }

        // Normalisasi kolom + rata-rata baris
        $w = [];
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += ($colSum[$j] > 0) ? $M[$i][$j] / $colSum[$j] : 0.0;
            }
            $w[$i] = $rowSum / $n;
        }

        return $w;
    }

    /**
     * Consistency Ratio.
     */
    public function calculateConsistencyRatio(array $M, array $w): array
    {
        $n = self::N;

        // WSV = M × w
        $wsv = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $wsv[$i] += $M[$i][$j] * $w[$j];
            }
        }

        // λmax
        $lambdaValues = [];
        for ($i = 0; $i < $n; $i++) {
            if ($w[$i] > 0) {
                $lambdaValues[] = $wsv[$i] / $w[$i];
            }
        }
        $lambdaMax = count($lambdaValues) > 0
            ? array_sum($lambdaValues) / count($lambdaValues)
            : $n;

        // CI & CR
        $ci = ($lambdaMax - $n) / ($n - 1);
        $cr = self::RI > 0 ? $ci / self::RI : 0;

        return [
            'lambda_max'    => round($lambdaMax, 6),
            'ci'            => round($ci, 6),
            'cr'            => round($cr, 6),
            'is_consistent' => $cr <= 0.10,
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────────────────────────

    private function formatWeights(array $w): array
    {
        return [
            'rasa'           => round($w[0], 4),
            'nutrisi'        => round($w[1], 4),
            'jenis_hidangan' => round($w[2], 4),
        ];
    }
}
