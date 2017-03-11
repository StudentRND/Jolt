<?php
namespace Jolt\Models;

class Campaign extends Model
{
    /////////////////////////
    // Validation
    /////////////////////////
    protected $rules = [
        'name'          => 'required|min:3|max:128',
        'url'           => 'active_url',
        'starts_at'     => 'date',
        'ends_at'       => 'date'
    ];

    /////////////////////////
    // Relations
    /////////////////////////
    public function users() { return $this->belongsToMany(User::class); }
    public function updates() { return $this->hasMany(CampaignUpdate::class); }

    /////////////////////////
    // Properties
    /////////////////////////
    
    /**
     * Gets a list of the top campaign contributors, ranked from first to last in terms of clicks.
     */
    public function getLeaderboardAttribute()
    {
        return User
            ::selectRaw('users.username, COUNT(link_clicks.id) as clicks')
            ->join('links', 'links.user_id', '=', 'users.id')
            ->join('link_clicks', 'link_clicks.link_id', '=', 'links.id')
            ->where('links.campaign_id', '=', $this->id)
            ->orderBy('clicks', 'DESC')
            ->groupBy('users.username')
            ->get();
    }


    /////////////////////////
    // Laravel
    /////////////////////////

    /**
     * Returns a list of dates, to be automatically serialized and deserialized to Carbon dates by Laravel.
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'starts_at', 'ends_at'];
    }

    /**
     * In general: configures static properties which would otherwise need to be set outside of the class.
     * This instance: generate an invite code automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $randomReadable = function($len) {
                $chars = str_split('abcdefghjkmnpqrstuvwxyz23456789');
                return implode('', array_map(function($i) use ($chars) { return $chars[$i]; }, array_rand($chars, $len)));
            };
            $model->invite = $randomReadable(4).'-'.$randomReadable(4);
        });
    }
}
