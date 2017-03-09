<?php
namespace Jolt\Models;

class Click extends Model
{
    public $table = "link_clicks";
    /////////////////////////
    // Relations
    /////////////////////////
    public function link() { return $this->belongsTo(Link::class); }
}
