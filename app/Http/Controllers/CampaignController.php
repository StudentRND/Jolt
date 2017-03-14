<?php
namespace Jolt\Http\Controllers;

use Jolt\Models;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    protected $campaign;
    public function __construct()
    {
        $this->campaign = Models\Campaign::find(\Route::current()->parameter('campaign'));
        \View::share('campaign', $this->campaign);
    }
    
    /////////////////////////
    // General Frontend
    /////////////////////////

    /**
     * Displays the campaign page
     */
    public function getIndex()
    {
        return \View::make('campaign/index');
    }

    /**
     * Accepts an invite and displays the welcome page, if any.
     */
    public function getInvite($inviteCode)
    {
        $campaign = Models\Campaign::where('invite', '=', $inviteCode)->firstOrFail();
        if (!Models\User::Me()->campaigns()->where('campaign_id', '=', $campaign->id)->exists()) {
            Models\User::Me()->campaigns()->attach($campaign);
        }

        return \redirect('/c/'.$campaign->id);
    }

    /////////////////////////
    // Updates
    /////////////////////////

    /**
     * Gets a JSON object containing all live-updating properties and a hash of the overall event config.
     */
    public function getState()
    {
        return json_encode([
            'version' => $this->campaign->updated_at->timestamp,
            'leaderboard' => $this->campaign->leaderboard,
            'updates' => array_map(function($x) { return ['text' => $x->text, 'author' => $x->user->username]; },
                            iterator_to_array($this->campaign->updates()->orderBy('created_at', 'DESC')->get())),
        ]);
    }

    public function getClicksTimeline()
    {
        $clicks = \DB::select('select DATE(link_clicks.created_at) as clicked_at, DATE(links.created_at) as created_at, COUNT(*) as count FROM link_clicks LEFT JOIN links ON (links.id = link_clicks.link_id) WHERE links.user_id = ? GROUP BY clicked_at, created_at', [Models\User::Me()->id]);

        return "created_at,clicked_at,count\n"
            .implode("\n", array_map(function($log){
                return implode(",", [$log->created_at, $log->clicked_at, $log->count]);
            }, $clicks));
    }

    /////////////////////////
    // User Promotion
    /////////////////////////
    
    public function getShare(Request $request, Models\Campaign $campaign, $site)
    {
        $subject = $this->campaign->default_email_subject;
        $message = $site === 'email' ? $this->campaign->default_email : $this->campaign->default_social_media;

        $link = new Models\Link;
        $link->campaign_id = $this->campaign->id;
        $link->user_id = Models\User::Me()->id;
        $link->type = $site;
        $link->save();

        $message = str_replace('[link]', $link->url, $message);

        $subject = urlencode($subject);
        $message = urlencode($message);
        $url = urlencode($link->url);

        switch ($site) {
            case "email":
                return \redirect("mailto:?to=&subject=$subject&body=$message");
            case "facebook":
                return \redirect("https://www.facebook.com/sharer/sharer.php?s=100&u=$url");
            case "twitter":
                return \redirect("http://twitter.com/share?text=$message&url=$url");
            case "linkedin":
                return \redirect("https://www.linkedin.com/cws/share?url=$url");
            default:
                $link->delete();
                \abort(404);
        }
    }

    /**
     * Creates a custom link for promotion.
     */
    public function postShareCustom(Request $request)
    {
        $link = new Models\Link;
        $link->campaign_id = $this->campaign->id;
        $link->user_id = Models\User::Me()->id;
        $link->type = Models\Link::TYPE_CUSTOM;
        $link->description = $request->description;
        $link->save();

        return json_encode(['description' => $link->description, 'url' => $link->url]);
    }
    
    /////////////////////////
    // Admin
    /////////////////////////

    /**
     * Displays the campaign information edit page.
     */
    public function getEdit(Request $request)
    {
        return \View::make('campaign/edit');
    }

    /**
     * Edits the campaign configuration.
     */
    public function postEdit(Request $request)
    {
        $this->campaign->name = $request->name;
        $this->campaign->default_social_media = $request->default_social_media??null;
        $this->campaign->default_email = $request->default_email??null;
        $this->campaign->default_email_subject = $request->default_email_subject??null;
        $this->campaign->url = $request->url;
        $this->campaign->domain = $request->domain??null;
        $this->campaign->starts_at = $request->starts_at;
        $this->campaign->ends_at = $request->ends_at;
        $this->campaign->save();

        return \redirect('/c/'.$this->campaign->id);
    }

    /**
     * Posts an admin update for display on the clients.
     */
    public function postUpdate(Request $request)
    {
        $update = new Models\CampaignUpdate;
        $update->campaign_id = $this->campaign->id;
        $update->user_id = Models\User::Me()->id;
        $update->text = $request->text;
        $update->save();

        return json_encode($update);
    }

    /**
     * Toggles a user's admin flag. Does not allow users to de-admin themselves, or for campaigns to have no admins.
     */
    public function postOpDeop(Request $request, Models\Campaign $campaign, $username)
    {
        $user = Models\User::where('username', '=', $username)->firstOrFail();
        if ($user->id === Models\User::Me()->id) \abort(400);

        // TODO: Cannot op a user who is not a member of the campaign
        $pivot = $user->campaigns()->where('campaign_id', '=', $campaign->id)->withPivot('is_admin')->first()->pivot;
        $pivot->is_admin = !$pivot->is_admin;
        $pivot->save();

        return json_encode(['is_admin' => $pivot->is_admin]);
    }
}
