<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class DefaultController extends Controller
{
    public function home()
    {
        // Récupère les 5 articles les plus récents avec les informations de l'utilisateur
        $posts = Post::latest()->take(5)->with('user')->get();
        
        return view('home', [
            'posts' => $posts    
        ]);
    }
}
