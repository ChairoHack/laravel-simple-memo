<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // メモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC') // ASC:小さい順、DESC:大きい順
            ->get();

        return view('create', compact('memos'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();
        //dd($posts);
        //dd(\Auth::id());

        Memo::insert([
            'content' => $posts['content'],
            'user_id' => \Auth::id()
        ]);

        return redirect(route('home'));
    }

    public function edit($id)
    {
        // メモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC') // ASC:小さい順、DESC:大きい順
            ->get();

        $edit_memo = Memo::find($id);

        return view('edit', compact('memos', 'edit_memo'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        //dd($posts);
        //dd(\Auth::id());

        Memo::where('id', $posts['memo_id'])->update([
            'content' => $posts['content']
        ]);

        return redirect(route('home'));
    }

    public function destory(Request $request)
    {
        $posts = $request->all();
        //dd($posts);
        //dd(\Auth::id());

        Memo::where('id', $posts['memo_id'])->update([
            'deleted_at' => date("Y-m-d H:i:s", time())
        ]);

        return redirect(route('home'));
    }
}
