<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Markdown;

class AboutController extends Controller
{
    public function show()
    {
        // Ambil deskripsi yang sudah disimpan dari database
        $description = DB::table('settings')->where('key', 'about_description')->first();

        // Konversi Markdown ke HTML menggunakan Laravel Markdown
        $htmlDescription = Markdown::convertToHtml($description->value);

        return view('about.about', compact('htmlDescription'));
    }
}
