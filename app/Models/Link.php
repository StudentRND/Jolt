<?php
namespace Jolt\Models;

class Link extends Model
{
    /////////////////////////
    // Constants
    /////////////////////////

    const TYPE_TWITTER = 'twitter';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_LINKEDIN = 'linkedin';
    const TYPE_INSTAGRAM = 'instagram';
    const TYPE_REDDIT = 'reddit';
    const TYPE_TUMBLR = 'tumblr';
    const TYPE_EMAIL = 'email';
    const TYPE_MESSENGER = 'fbmessenger';
    const TYPE_CUSTOM = 'custom';

    /////////////////////////
    // Relations
    /////////////////////////

    public function clicks() { return $this->hasMany(Click::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function campaign() { return $this->belongsTo(Campaign::class); }

    /////////////////////////
    // Attributes
    /////////////////////////

    public function getUrlAttribute()
    {
        return 'http://'.($this->campaign->domain??config('app.link_domain')).'/'.$this->id;
    }

    /////////////////////////
    // Laravel
    /////////////////////////
    public $incrementing = false;
    /**
     * In general: configures static properties which would otherwise need to be set outside of the class.
     * This instance: generate a random link ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = strtolower(str_random(6));
        });
    }
}
