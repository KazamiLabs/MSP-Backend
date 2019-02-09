@extends('layouts.md')

@section('content')
<div class="container">
    <div class="row justify-content-center" style="width: 100%;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">List</div>

                <div class="card-body">
                    <ol>
                        @foreach ($posts as $post)
                            <li>
                                {{ $post->post_title }}
                                {{ $post->author->nicename }}
                            </li>
                        @endforeach
                    </ol>
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
