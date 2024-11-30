<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Webpage;


class SetPageAttributes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $webpage = Webpage::where(function ($query) use ($request) {
            $query->where('uri', $request->path())
                  ->orWhere(function($subQuery) use ($request) {
                      $subQuery->where('name', $request->route()->getName())
                               ->whereNotNull('name');
                  });
        })->first();
        
        if($webpage){

            // Now set these meta tags in view or share them with all views
            view()->share('set_title', $webpage->title);
            view()->share('set_description', $webpage->description);

        }


        

        return $next($request);
    }
}
