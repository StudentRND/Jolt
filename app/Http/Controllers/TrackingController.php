<?php
namespace Jolt\Http\Controllers;

use Jolt\Models;
use Jolt\Exceptions;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function getIndex(Request $request, Models\Link $link)
    {
        if (!Models\Click::where('ip', '=', $request->ip())->exists() || \config('app.debug')) {
            $click = new Models\Click;
            $click->link_id = $link->id;
            $click->lat = 0;
            $click->lng = 0;
            $click->ip = $request->ip();
            $click->save();
        }

        $attribution = [
            'utm_medium' => 'Volunteer Promotion',
            'utm_source' => 'Jolt',
            'utm_campaign' => $link->campaign->name,
            'utm_content' => $link->user->username.' '.$link->type
        ];
        $redirectUrl = $link->campaign->url.'?'.http_build_query($attribution);

        return \redirect($redirectUrl);
    }
}
