<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MyClientController extends Controller
{
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client,slug',
            'is_project' => 'in:0,1',
            'self_capture' => 'in:0,1',
            'client_prefix' => 'required|string|max:4',
            'client_logo' => 'image|max:2048',
        ]);

        $clientLogoUrl = 'no-image.jpg';
        if ($request->hasFile('client_logo')) {
            $clientLogoUrl = Storage::disk('s3')->put('clients', $request->file('client_logo'));
            $clientLogoUrl = Storage::disk('s3')->url($clientLogoUrl);
        }

        $data = $request->all();
        $data['client_logo'] = $clientLogoUrl;

        $client = MyClient::create($data);

        Redis::set($client->slug, $client->toJson());

        return response()->json($client, 201);
    }

    public function show($slug)
    {
        if (Redis::exists($slug)) {
            $json = Redis::get($slug);
            return response($json, 200)->header('Content-Type', 'application/json');
        }

        $client = MyClient::where('slug', $slug)->firstOrFail();
        Redis::set($client->slug, $client->toJson());
        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = MyClient::findOrFail($id);
        $client->fill($request->except('client_logo'));

        if ($request->hasFile('client_logo')) {
            $clientLogoUrl = Storage::disk('s3')->put('clients', $request->file('client_logo'));
            $clientLogoUrl = Storage::disk('s3')->url($clientLogoUrl);
            $client->client_logo = $clientLogoUrl;
        }

        $client->save();

        Redis::del($client->slug);
        Redis::set($client->slug, $client->toJson());

        return response()->json($client);
    }

    public function destroy($id)
    {
        $client = MyClient::findOrFail($id);
        $client->deleted_at = now();
        $client->save();

        Redis::del($client->slug);

        return response()->json(['status' => 'deleted']);
    }
}
