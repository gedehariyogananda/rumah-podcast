<?php

namespace App\Http\Controllers;

use App\Models\RecordingPodcast;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecordingPodcastController extends Controller
{
    private $user;
    private $recording;

    public function __construct(User $user, RecordingPodcast $recording)
    {
        $this->user = $user;
        $this->recording = $recording;
    }

    public function index()
    {
        $user = $this->user->where('id', auth()->user()->id)->first();
        $podcasts = $this->recording->where('user_id', auth()->user()->id)->get();
        return view('podcasts.user_podcast', compact('podcasts', 'user'));
    }

    public function getRecording()
    {
        $user = $this->user->where('id', auth()->user()->id)->first();
        return view('podcasts.recording', compact('user'));
    }


    public function addpodcast(Request $request)
    {
        $request->validate([
            'audio' => 'required',
        ]);

        $file = $request->file('audio');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $newName = rand(1, 20) . $timestamp . '.mp3';

        $path = $file->storeAs('audio', $newName, 'public');


        $successAdded = $this->recording->create([
            'recording' => $path,
            'user_id' => auth()->user()->id,
            'title_podcast' => "",
            'photo' => "",
            'genre_podcast' => "",
            'slug' => uniqid(),
            'description' => ""
        ]);

        $idRecording = $successAdded->slug;

        return response()->json(['path' => $path, 'message' => 'Upload successful', 'idRecording' => $idRecording], 200);
    }

    public function setRecording($slug)
    {
        $user = $this->user->where('id', auth()->user()->id)->first();
        $podcast = $this->recording->where('slug', $slug)->first();
        return view('podcasts.create', compact('podcast', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_podcast' => 'required',
            'photo' => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
            'genre_podcast' => 'required',
            'description' => 'required|string',
        ]);

        $podcast = $this->recording->where('slug', $request->slug)->first();

        $podcast->title_podcast = $request->title_podcast;
        $podcast->genre_podcast = $request->genre_podcast;
        $podcast->description = $request->description;
        $podcast->slug = $request->genre_podcast . '_' . $request->title_podcast . '_' . rand(1, 10);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $newName = $timestamp . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('photo', $newName, 'public');
            $podcast->photo = $path;
        }

        $podcast->save();

        return redirect()->route('recording.index-podcast')->with('success', 'Podcast has been created successfully');
    }

    public function destroy($slug)
    {
        $podcast = $this->recording->where('slug', $slug)->first();
        $podcast->delete();

        return redirect()->route('recording.index-podcast')->with('success', 'Podcast has been deleted successfully');
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'title_podcast' => 'required',
            'photo' => 'file|image|mimes:jpeg,png,jpg|max:20399221',
            'genre_podcast' => 'required',
            'recording' => 'file|max:20482024',
            'description' => 'required|string',
        ]);

        $podcast = $this->recording->where('slug', $slug)->first();

        $podcast->title_podcast = $request->title_podcast;
        $podcast->genre_podcast = $request->genre_podcast;
        $podcast->description = $request->description;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $newName = $timestamp . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('photo', $newName, 'public');
            $podcast->photo = $path;
        }

        if ($request->hasFile('recording')) {
            $file2 = $request->file('recording');
            $timestamp2 = Carbon::now()->format('Y-m-d_H-i-s');
            $newName2 = rand(1, 20) . $timestamp2 . '.' . $file2->getClientOriginalExtension();

            $path2 = $file2->storeAs('audio', $newName2, 'public');
            $podcast->recording = $path2;
        }

        $podcast->save();

        return redirect()->route('recording.index-podcast')->with('success', 'Podcast has been updated successfully');
    }


    public function getAllDataUsers()
    {
        $users = $this->user->all();
        return view('admin.index', compact('users'));
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $podcasts = $this->recording->where('title_podcast', 'like', '%' . $search . '%')->get();
        return view('podcasts.index', compact('podcasts'));
    }

    public function delete($slug)
    {
        $podcast = $this->recording->where('slug', $slug)->first();
        $podcast->delete();

        return back()->with('success', 'Podcast has been deleted successfully');
    }

    // ------------------ admin ------------------------------------//

    public function getAllDataPodcasts()
    {
        $podcasts = $this->recording->with('user')->get();
        return view('admin.index_podcast', compact('podcasts'));
    }

    public function deletePodcastUser($slug)
    {
        $podcast = $this->recording->where('slug', $slug)->first();
        $podcast->delete();

        return back()->with('success', 'Podcast has been deleted successfully');
    }

    public function addPodcastUser(Request $request)
    {
        $request->validate([
            'title_podcast' => 'required',
            'photo' => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
            'genre_podcast' => 'required',
            'recording' => 'required|file|mimes:mp3|max:20482024',
            'description' => 'required|string',
        ]);

        $file1 = $request->file('recording');
        $timestamp1 = Carbon::now()->format('Y-m-d_H-i-s');
        $newName1 = rand(1, 20) . $timestamp1 . '.mp3';
        $path1 = $file1->storeAs('audio', $newName1, 'public');

        $file2 = $request->file('photo');
        $timestamp2 = Carbon::now()->format('Y-m-d_H-i-s');
        $newName2 = $timestamp2 . '.' . $file2->getClientOriginalExtension();
        $path2 = $file2->storeAs('photo', $newName2, 'public');

        $this->recording->create([
            'user_id' => auth()->user()->id,
            'title_podcast' => $request->title_podcast,
            'photo' => $path2,
            'genre_podcast' => $request->genre_podcast,
            'recording' => $path1,
            'slug' => uniqid(),
            'description' => $request->description
        ]);

        return back()->with('success', 'Podcast has been added successfully');
    }
}
