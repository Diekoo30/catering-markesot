<?php

use App\Http\Controllers\RecommendationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Catering Markesot
|--------------------------------------------------------------------------
|
| Routes di sini secara otomatis di-prefix dengan "/api" dan
| dilindungi oleh middleware "api" (stateless, tanpa session).
|
| Endpoint AHP Recommendation:
|   POST /api/recommendation
|
| Payload (JSON):
|   {
|     "matrix": [
|       [1,    3,    5,    7   ],
|       [0.33, 1,    3,    5   ],
|       [0.2,  0.33, 1,    3   ],
|       [0.14, 0.2,  0.33, 1   ]
|     ]
|   }
|
*/

Route::post('/recommendation', [RecommendationController::class, 'getRecommendation'])
    ->name('api.recommendation');
