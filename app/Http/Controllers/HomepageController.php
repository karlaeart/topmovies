<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomepageController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getMovies(Request $request) {

        // check if certain year is requested else use current year
        if ($request->has('year')) {
            $year = $request->input('year');
        } else {
            $year = now()->year;
        }

        // fetch top 20 movies of requested year
        $response = Http::withHeaders([
            'x-rapidapi-key' => 'e21b4d9dedmshe475c8eff16ec1bp19ab03jsne8fe19c8afef',
            'x-rapidapi-host' => 'movies-tvshows-data-imdb.p.rapidapi.com'
        ])->get('https://movies-tvshows-data-imdb.p.rapidapi.com/', [
            'type' => 'get-popular-movies',
            'page' => 1,
            'year' => $year,
        ]);

        $movies = [];

        foreach ($response['movie_results'] as $movie) {

            // fetch movie details
            $details = Http::withHeaders([
                'x-rapidapi-key' => 'e21b4d9dedmshe475c8eff16ec1bp19ab03jsne8fe19c8afef',
                'x-rapidapi-host' => 'movies-tvshows-data-imdb.p.rapidapi.com'
            ])->get('https://movies-tvshows-data-imdb.p.rapidapi.com/', [
                'type' => 'get-movie-details',
                'imdb' => $movie['imdb_id'],
            ]);

            // fetch movie images
            $images = Http::withHeaders([
                'x-rapidapi-key' => 'e21b4d9dedmshe475c8eff16ec1bp19ab03jsne8fe19c8afef',
                'x-rapidapi-host' => 'movies-tvshows-data-imdb.p.rapidapi.com'
            ])->get('https://movies-tvshows-data-imdb.p.rapidapi.com/', [
                'type' => 'get-movies-images-by-imdb',
                'imdb' => $movie['imdb_id'],
            ]);

            $details_json = $details->json();

            $details_json['id'] = $movie['imdb_id'];
            $details_json['poster'] = $images['poster'];

            $movies[] = $details_json;
        }

        return view('welcome', compact('movies', 'year'));
    }
}
