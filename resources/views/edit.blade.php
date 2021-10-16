@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            メモ編集
            <form class="card-body" action="{{ route('destory') }} " method="POST">
                @csrf
                <input type="hidden" name="memo_id" value="{{ $edit_memo[0]['id'] }}">
                <button type="submit" class="btn btn-secondary">削除</button>
            </form>
        </div>
        {{-- route('store')とかくと/store --}}
        <form class="card-body" action="{{ route('update') }} " method="POST">
            @csrf
            <input type="hidden" name="memo_id" value="{{ $edit_memo[0]['id'] }}">
            <div class="form-group">
                <textarea class="form-control" name="content" rows="3"
                    placeholder="ここにメモを入力">{{ $edit_memo[0]['content'] }}</textarea>
            </div>
            @foreach ($tags as $tag)
                <div class="form-check form-check-inline mb-3">
                    {{-- 3高演算子 --}}
                    <input type="checkbox" class="form-check-input" name="tags[]" id="{{ $tag['id'] }}"
                        value="{{ $tag['id'] }}" {{ in_array($tag['id'], $include_tags) ? 'checked' : '' }}>
                    <label for="{{ $tag['id'] }}" class="form-check-label">{{ $tag['name'] }}</label>
                </div>
            @endforeach
            <input type="text" class="form-control w-50 mb-3" name="new_tag" placeholder="新しいタグを入力">
            <button type="submit" class="btn btn-primary">更新</button>
        </form>
    </div>
@endsection
