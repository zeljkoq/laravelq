<?php

namespace App\Http\Controllers;


use App\Http\Resources\AdminResource;
use App\Http\Resources\SongResource;
use App\Http\Resources\UserResource;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSongRequest;
use Illuminate\Support\Facades\Auth;


/**
 * Class SongsController
 * @package App\Http\Controllers
 */
class SongsController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('songs.index')->with([
            'currentUser' => auth()->user(),
        ]);
    }

    /**
     * @param $user_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function user($user_id)
    {
        return view('user.index')->with([
            'user' => $user_id,
        ]);
    }

    /**
     * @param StoreSongRequest $request
     * @param Song $song
     * @return SongResource
     */
    public function store(StoreSongRequest $request, Song $song)
    {

        $song->artist = $request->artist;
        $song->track = $request->track;
        $song->link = $request->link;

        $song->user_id = $request->user()->id;
        $song->save();

        return new SongResource($song);

    }

    /**
     * @param Request $request
     * @param $user_id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getUserData(Request $request, $user_id)
    {


        if (isset($user_id)) {

            $user = User::find($user_id);

            if ($user->hasRole('admin')) {
                $songs = Song::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(5);

                return AdminResource::collection($songs);
            }

            if ($user->hasRole('user')) {
                $songs = Song::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(5);

                return UserResource::collection($songs);
            }
            $songs = Song::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(5);
            return SongResource::collection($songs);

        }
    }


    /**
     * @param $song_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editIndex($song_id)
    {
        return view('songs.edit')->with([
            'song_id' => $song_id,
        ]);
    }

    /**
     * @param $song_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editData($song_id)
    {
        $song = Song::where('id', $song_id)->first();
        return response()->json([
            'song' => $song
        ]);

    }

    /**
     * @param $song_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($song_id)
    {
        if (isset($song_id)) {

            $user = User::findOrFail(auth()->user()->id);

            if ($user->hasRole('admin')) {
                $song = Song::where('id', $song_id)->first();
                $song->delete();
                return new SongResource($song);
            }

            if ($user->hasRole('user')) {
                $song = Song::where('id', $song_id)->first();
                if ($song->user_id == $user->id) {
                    $song->delete();
                    return new SongResource($song);
                }

            }

            return response()->json([
                'message' => 'You don\'t have permission to delete this song.'
            ]);

        }

    }


    /**
     * @param StoreSongRequest $request
     * @param $song_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreSongRequest $request, $song_id)
    {

//        return auth()->user()->id;

        if (isset($song_id)) {

            $user = User::findOrFail(auth()->user()->id);



            if ($user->hasRole('admin')) {
                $song = Song::where('id', $song_id)->first();
                $song->artist = $request->artist;
                $song->track = $request->track;
                $song->link = $request->link;
                $song->update();
                return new SongResource($song);
            }

            if ($user->hasRole('user')) {
                $song = Song::where('id', $song_id)->first();
                if ($song->user_id == $user->id) {
                    $song = Song::where('id', $song_id)->first();
                    $song->artist = $request->artist;
                    $song->track = $request->track;
                    $song->link = $request->link;
                    $song->update();
                    return new SongResource($song);
                }

            }

            return response()->json([
                'message' => 'You don\'t have permission to change this song.'
            ]);

        }

    }
}
