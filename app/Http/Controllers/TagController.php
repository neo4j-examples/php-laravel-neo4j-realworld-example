<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use function response;

class TagController extends Controller
{
    public function listTags(): JsonResponse
    {
        return response()->json(['tags' => Tag::query()->orderBy('name')->pluck('name')]);
    }
}
