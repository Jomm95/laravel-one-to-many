<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use App\Category;
use Illuminate\Http\Request;

//per usare funzione per lo slug
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //preleva i dati sui post
        $posts = Post::all();

        // passa dati alla vista
        return view('admin.post.index', compact('posts'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //prelevo le categorie
        $categories = Category::all();
        return view('admin.post.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validazioni
        $request->validate(
            [
                'title' => 'required|min:5',
                'content' => 'required|min:10',

                // accetto category_id se  esiste nella tabella categories alla colonna id
                "category_id"=>'nullable|exists:categories,id'
            ]
            );

            // prelevo dati dal form
            $data = $request->all();

            //definisco lo slug - funzione laravel di STR (in alto: use Illuminate\Support\Str;)
            $slug = Str::slug($data['title']);

            //definisco funzione che fa si che lo slug non sia lo stesso se due titoli sono uguali.
            //imposto un counter, poi ciclo while: query sugli slug di post se trova corrispondenza fa un append allo slug del contatore,
            //incrementa il contatore. Se non trova corrispondenza esce dal ciclo while.
            $counter = 1;

            //potrei togliere "=" e sarebbe comunque un operatore di uguaglianza
            while(Post::where('slug', '=',  $slug)->first()){
                
                $slug = Str::slug($data['title']) . '-' . $counter;
                $counter++;

            }

            // inserisco lo slug dentro data
            $data['slug'] = $slug;

            $post = new Post();

            //fill su post
            $post->fill($data);
            // salvo
            $post->save();

            //decido il redirect
            return redirect()->route('admin.posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.post.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('admin.post.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //validazioni
        $request->validate(
            [
                'title' => 'required|min:5',
                'content' => 'required|min:10',
            ]
            );

            // prelevo dati dal form
            $data = $request->all();

            //definisco lo slug - funzione laravel di STR (in alto: use Illuminate\Support\Str;)
            $slug = Str::slug($data['title']);

            //qualora lo slug sia diverso da quello originale del post allora eseguo il ciclo while come per store
            if ($post->slug != $slug) {
                $counter = 1;
                while(Post::where('slug', $slug)->first()){
                    $slug = Str::slug($data['title']) . "-". $counter;
                    $counter++;
                };
                $data['slug']=$slug;
            }

            //fill su post
            $post->update($data);
            // salvo
            $post->save();

            //decido il redirect
            return redirect()->route('admin.posts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index');
    }
}
