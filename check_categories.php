<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CATEGORIES ===" . PHP_EOL;
foreach (DB::table('categories')->get() as $c) {
    echo $c->id . ' | ' . $c->name . PHP_EOL;
}

echo PHP_EOL . "=== MENU ITEMS & CATEGORIES ===" . PHP_EOL;
foreach (DB::table('menu_items')->get() as $m) {
    $cat = DB::table('categories')->where('id', $m->category_id)->first();
    echo $m->id . ' | ' . $m->name . ' | Category: ' . ($cat ? $cat->name : 'None') . PHP_EOL;
}
