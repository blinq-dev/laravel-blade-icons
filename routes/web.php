<?php

use Blinq\Icons\Components\Icon;
use Blinq\Icons\IconPack;
use Blinq\Icons\Utils;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Compilers\BladeCompiler;

Route::get('/blinq-icons/js/blinq-icons.js', function () {
    return Utils::pretendResponseIsFile(__DIR__ . "/../resources/js/blinq-icons.js");
});

Route::get('/blinq-icons/lazy/{ids}', function () {
    try {
        $ids = str(request()->route('ids'))->explode("|");
        $renderedItems = [];
        foreach($ids as $id) {
            $id = base64_decode($id);

            $pack = str($id)->before(' ');
            $name = str($id)->after(' ');

            $renderedItems[] = IconPack::svg($pack, $name);
        }
        // return json with cache headers
        return response()->json($renderedItems)->header('Cache-Control', 'max-age=31536000, public');
    } catch(\Exception $e) {
        return response()->make($e->getMessage(), 500);
    }
});