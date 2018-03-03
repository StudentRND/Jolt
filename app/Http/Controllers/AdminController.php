<?php
namespace Jolt\Http\Controllers;

use Jolt\Models;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getIndex()
    {
        return \View::make('admin/index', [
            'campaigns' => Models\Campaign::all(),
            'users' => Models\User::all(),
        ]);
    }

    public function getNew(Request $request)
    {
        return \View::make('campaign/edit');
    }

    public function postNew(Request $request)
    {
        $campaign = new Models\Campaign;
        $campaign->name = $request->name;
        $campaign->default_social_media = $request->default_social_media??null;
        $campaign->default_email = $request->default_email??null;
        $campaign->default_email_subject = $request->default_email_subject??null;
        $campaign->url = $request->url;
        $campaign->domain = $request->domain??null;
        $campaign->starts_at = $request->starts_at;
        $campaign->ends_at = $request->ends_at;
        $campaign->save();

        return \redirect('/c/'.$campaign->id.'/edit');
    }
}
