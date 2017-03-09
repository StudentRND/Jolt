<?php
namespace Jolt\Models;

class CampaignUpdate extends Model
{
    /////////////////////////
    // Relations
    /////////////////////////
    public function campaign() { return $this->belongsTo(Campaign::class); }
    public function user() { return $this->belongsTo(User::class); }
}
