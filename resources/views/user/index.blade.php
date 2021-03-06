@extends('layouts.app')

@section('content')
    <div class="container" id="allSongs">
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        Add a song
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            {{--<form action="{{route('addsong')}}" method="POST">--}}
                            <div class="form-row">
                                <div class="col-4">
                                    <input type="text" name="artist" id="artist" class="form-control" placeholder="">
                                </div>
                                <div class="col">
                                    <input type="text" name="track" id="track" class="form-control" placeholder="">
                                </div>
                                <div class="col">
                                    <input type="text" name="link" id="link" class="form-control" placeholder="">
                                </div>
                                <button class="btn btn-primary" type="button" id="addSong">Add</button>
                            </div>
                            {{--</form>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        Songs

                    </div>
                    <div class="card-body">
                        <div>
                            <div id="emptySongs" class="table-responsive">
                                <table class="table table-striped">
                                    <thead style="background-color: #ddd; font-weight: bold;">
                                    <tr>
                                        <td>Artist</td>
                                        <td>Track</td>
                                        <td>Link</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </thead>
                                    <tbody id="songsList"></tbody>
                                </table>
                            </div>
                            <div id="pagination">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function getIndexData() {
            $.ajax({
                url: "{{route('song.get.user.data', auth()->user()->id)}}",
                contentType: "application/json",
                success: function (songs) {
                    console.log(songs);
                    $.ajax({
                        url: "{{route('song.get.user.data', $user)}}",
                        contentType: "application/json",
                        success: function (data) {
                            // console.log(data);
                            var html = '';
                            for (i = 0; i < data.data.length; i++) {
                                if (data.data[i].admin === '1') {
                                    html += '<tr>' +
                                        '<td hidden class="songId">' + data.data[i].id + '</td>' +
                                        '<td id="art">' + data.data[i].artist + '</td>' +
                                        '<td id="trck">' + data.data[i].track + '</td>' +
                                        '<td id="lnk"><a id="atr" target="_blank" href="' + data.data[i].link + '">' + data.data[i].link + '</a></td>' +
                                        '<td><a href="' + data.data[i].edit + '" class="btn btn-light"><i class="fas fa-edit"></i></a></td>' +
                                        '<td><button id="deleteSong" class="btn btn-danger" href=""><i class="fas fa-trash-alt"></i></button></td>' +
                                        '</tr>';
                                }
                                else if (data.data[i].admin === '2') {
                                    html += '<tr>' +
                                        '<td hidden class="songId">' + data.data[i].id + '</td>' +
                                        '<td id="art">' + data.data[i].artist + '</td>' +
                                        '<td id="trck">' + data.data[i].track + '</td>' +
                                        '<td id="lnk"><a id="atr" target="_blank" href="' + data.data[i].link + '">' + data.data[i].link + '</a></td>' +
                                        '</tr>';
                                }
                                else {
                                    html += '<tr>' +
                                        '<td hidden class="songId">' + data.data[i].id + '</td>' +
                                        '<td id="art">' + data.data[i].artist + '</td>' +
                                        '<td id="trck">' + data.data[i].track + '</td>' +
                                        '<td id="lnk"><a id="atr" target="_blank" href="' + data.data[i].link + '">' + data.data[i].link + '</a></td>' +
                                        '</tr>';
                                }
                            }
                            var pagination = '';
                            pagination += '<nav aria-label="Page navigation example">' +
                                '<ul class="pagination">' +
                                '<li class="page-item"><a class="page-link" href="'+songs.links.prev+'">Previous</a></li>' +
                                '<li class="page-item"><a class="page-link" href="'+songs.links.next+'">Next</a></li>' +
                                '</ul>' +
                                '</nav>';
                            $('#songsList').html(html);
                            $('#pagination').html(pagination);
                        }
                    });
                }
            });
        }

        $(document).ready(function () {
            getIndexData();
        });

        $('#addSong').click(function () {
            var artist = $('#artist').val();
            var track = $('#track').val();
            var link = $('#link').val();
            var user_id = '{{ Auth()->user()->id }}';
            $.ajax({
                type: "post",
                url: '{{route('song.store')}}',
                data: ({artist: artist, track: track, link: link, user_id: user_id}),
                success: function (response) {
                    $('#artist').val('');
                    $('#track').val('');
                    $('#link').val('');
                    html = '';
                    html += '<tr>' +
                        '<td hidden class="songId">' + response.song.id + '</td>' +
                        '<td id="art">' + response.song.artist + '</td>' +
                        '<td id="trck">' + response.song.track + '</td>' +
                        '<td id="lnk"><a id="atr" target="_blank" href="' + response.song.link + '">' + response.song.link + '</a></td>' +
                        '</tr>';
                    $('#songsList').prepend(html);
                }
            });
        });

        $('body').on('click', '#deleteSong', function () {
            var $row = $(this).closest("tr");
            var songId = $row.find(".songId").html();
            // console.log(songId);
            $.ajax({
                type: "GET",
                url: '/api/song/delete/'+songId,
                data: $(this).serialize(),
                contentType: "application/json",
                success: function (response) {
                    // response = JSON.stringify(response);
                    console.log(response);
                    $('td:contains("' + response.song + '")').parent().css("display", "none");
                }
            });
        });

        $(window).on('hashchange', function() {
            if (window.location.hash) {
                var page = window.location.hash.replace('#', '');
                if (page == Number.NaN || page <= 0) {
                    return false;
                } else {
                    getPosts(page);
                }
            }
        });
        $(document).ready(function() {
            $(document).on('click', '#pagination a', function (e) {
                getPosts($(this).attr('href').split('page=')[1]);
                e.preventDefault();
            });
        });
        function getPosts(page) {
            $.ajax({
                url : '/api/song/{{$user}}?page=' + page,
                dataType: 'json',
            }).done(function (songs) {
                var html = '';
                for (i = 0; i < songs.data.length; i++) {
                    if (songs.data[i].admin === '1') {
                        html += '<tr>' +
                            '<td hidden class="songId">' + songs.data[i].id + '</td>' +
                            '<td id="art">' + songs.data[i].artist + '</td>' +
                            '<td id="trck">' + songs.data[i].track + '</td>' +
                            '<td id="lnk"><a id="atr" target="_blank" href="' + songs.data[i].link + '">' + songs.data[i].link + '</a></td>' +
                            '<td><a href="' + songs.data[i].edit + '" class="btn btn-light"><i class="fas fa-edit"></i></a></td>' +
                            '<td><button id="deleteSong" class="btn btn-danger" href=""><i class="fas fa-trash-alt"></i></button></td>' +
                            '</tr>';
                    }
                    else if (songs.data[i].admin === '2') {
                        html += '<tr>' +
                            '<td hidden class="songId">' + songs.data[i].id + '</td>' +
                            '<td id="art">' + songs.data[i].artist + '</td>' +
                            '<td id="trck">' + songs.data[i].track + '</td>' +
                            '<td id="lnk"><a id="atr" target="_blank" href="' + songs.data[i].link + '">' + songs.data[i].link + '</a></td>' +
                            '</tr>';
                    }
                    else {
                        html += '<tr>' +
                            '<td hidden class="songId">' + songs.data[i].id + '</td>' +
                            '<td id="art">' + songs.data[i].artist + '</td>' +
                            '<td id="trck">' + songs.data[i].track + '</td>' +
                            '<td id="lnk"><a id="atr" target="_blank" href="' + songs.data[i].link + '">' + songs.data[i].link + '</a></td>' +
                            '</tr>';
                    }
                }
                $('#songsList').html(html);
                location.hash = page;
            }).fail(function () {
                alert('Posts could not be loaded.');
            });
        }
    </script>
@endsection
