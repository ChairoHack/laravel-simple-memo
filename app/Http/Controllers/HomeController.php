<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\MemoTag;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

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
        $tags = Tag::select('tags.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();

        return view('create', compact('tags'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();
        $request->validate(['content' => 'required']);
        //dd($posts);
        //dd(\Auth::id());
        //トランザクション開始
        //新規タグが入力されているかチェック
        //新規タグが既にtagsテーブルに存在するかのチェック
        //新規タグが既に存在しなければ、tagsテーブルにインサート→IDを取得
        //トランザクション終了
        DB::transaction(function () use ($posts) {
            // メモIDをインサートして取得
            $memo_id = Memo::insertGetId([
                'content' => $posts['content'],
                'user_id' => \Auth::id()
            ]);
            // タグがTagテーブルに存在するかを取得
            $tag_exists = Tag::where('user_id', '=', \Auth::id())
                ->where('name', '=', $posts['new_tag'])
                ->exists();
            // 新規タグが入力されている かつ 新規タグがTagテーブルに同じものが存在しない場合
            // 新規タグをTagテーブルにインサートして、タグIDを取得
            if (!empty($posts['new_tag']) && !$tag_exists) {
                $tag_id = Tag::insertGetId([
                    'user_id' => \Auth::id(),
                    'name' => $posts['new_tag']
                ]);
                // タグが登録されたときはメモとタグを紐付ける
                MemoTag::insert([
                    'memo_id' => $memo_id,
                    'tag_id' => $tag_id
                ]);
            }
            //既存タグを紐付け
            foreach ($posts['tags'] as $tag) {
                MemoTag::insert([
                    'memo_id' => $memo_id,
                    'tag_id' => $tag
                ]);
            }
        });


        return redirect(route('home'));
    }

    public function edit($id)
    {
        //$edit_memo = Memo::find($id);
        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
            ->leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
            ->leftJoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get();
        $include_tags = [];
        foreach ($edit_memo as $memo) {
            array_push($include_tags, $memo['tag_id']);
        }

        $tags = Tag::select('tags.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC') // ASC:小さい順、DESC:大きい順
            ->get();

        return view('edit', compact('edit_memo', 'include_tags', 'tags'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        $request->validate(['content' => 'required']);
        //dd($posts);
        //dd(\Auth::id());
        DB::transaction(function () use ($posts) {
            Memo::where('id', $posts['memo_id'])->update([
                'content' => $posts['content']
            ]);
            MemoTag::where('memo_id', '=', $posts['memo_id'])->delete();
            foreach ($posts['tags'] as $tag) {
                MemoTag::insert([
                    'memo_id' => $posts['memo_id'],
                    'tag_id' => $tag
                ]);
            }
            // タグがTagテーブルに存在するかを取得
            $tag_exists = Tag::where('user_id', '=', \Auth::id())
                ->where('name', '=', $posts['new_tag'])
                ->exists();
            // 新規タグが入力されている かつ 新規タグがTagテーブルに同じものが存在しない場合
            // 新規タグをTagテーブルにインサートして、タグIDを取得
            if (!empty($posts['new_tag']) && !$tag_exists) {
                $tag_id = Tag::insertGetId([
                    'user_id' => \Auth::id(),
                    'name' => $posts['new_tag']
                ]);
                // タグが登録されたときはメモとタグを紐付ける
                MemoTag::insert([
                    'memo_id' => $posts['memo_id'],
                    'tag_id' => $tag_id
                ]);
            }
        });

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
