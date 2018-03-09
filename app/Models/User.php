<?php
namespace Jolt\Models;

class User extends Model
{
    /////////////////////////
    // Validation
    /////////////////////////
    protected $rules = [
        'username'      => 'required|alpha_dash|min:3|max:16|unique:users',
        'email'         => 'required|email',
        'password'      => 'required|min:5|max:255', // Not hashed when validated if password was updated.
        'is_superadmin' => 'boolean'
    ];

    /////////////////////////
    // Relations
    /////////////////////////
    public function campaigns() { return $this->belongsToMany(Campaign::class); }
    public function links() { return $this->hasMany(Link::class); }
    public function clicks() { return $this->hasManyThrough(Click::class, Link::class); }

    /////////////////////////
    // Functions
    /////////////////////////

    public function IsAdminFor(Campaign $campaign)
    {
        $record = $this->campaigns()->where('campaign_id', '=', $campaign->id)->withPivot('is_admin')->first();

        return $this->is_superadmin || (isset($record) && $record->pivot->is_admin);
    }

    /////////////////////////
    // Login
    /////////////////////////
    public static function IsLoggedIn()
    {
        return self::where('id', '=', \request()->session()->get('me'))->first() !== null;
    }

    /**
     * Gets the currently logged in user, or throws an authentication exception.
     */
    public static function Me()
    {
        if (!isset(self::$_me)) {
            self::$_me = self::where('id', '=', \request()->session()->get('me'))->first();
            if (!isset(self::$_me)) \abort(403);
        }
        return self::$_me;
    }
    private static $_me = null;

    /**
     * Logs the user in for the current session.
     */
    public function Login()
    {
        \request()->session()->put('me', $this->id);
    }

    /**
     * Logs the user out for the current session.
     */
    public function Logout()
    {
        \request()->session()->forget('me');
    }

    public function MintJwt($aud)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode(['sub' => $this->id, 'iss' => time(), 'exp' => time() + (60*60*24), 'aud' => $aud]);
        $data = implode('.', [self::base64UrlEncode($header), self::base64UrlEncode($payload)]);
        $sig = hash_hmac('SHA256', $data, \config('app.key'));
        return $data.'.'.$sig;
    }

    public static function FromJwt($jwt, $aud)
    {
        list($header, $payload, $sig) = explode('.', $jwt);
        if (hash_hmac('SHA256', $header.'.'.$payload, \config('app.key')) !== $sig) return null;

        $header = json_decode(self::base64UrlDecode($header));
        $payload = json_decode(self::base64UrlDecode($payload));
        if ($payload->aud !== $aud || $payload->iss > time() || $payload->exp < time()) return null;


        return self::where('id', '=', $payload->sub)->first();
    }

    /////////////////////////
    // Authentication
    /////////////////////////
    
    /**
     * Checks whether the given password matches the current user's password. This should only be used for re-checking
     * a password, never for login (because the time to check the passwords differs substantially depending on whether
     * the user exists or not). Use self::VerifyLogin for logins.
     *
     * @param   string  $password   The password to check.
     * @return  bool                true if the password matches, otherwise false.
     */
    public function VerifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Updates the user's password, using the most recent secure practices (bcrypt at the time of writing). Doesn't
     * save the user, so you'll still need to call ->save() as usual.
     * 
     * @param   string  $newPassword    The new password.
     * @return  void
     */
    public function UpdatePassword($newPassword)
    {
        $this->password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Validates whether the given credentials are correct (trying to take roughly the same amount of time regardless
     * of whether a user exists or not.)
     * 
     * @param   string  $username   The username to check.
     * @param   string  $password   The password associated with the username.
     * @return  bool                true if the username and password match, otherwise false.
     */
    public static function VerifyLogin($username, $password)
    {
        $user = self::where('username', '=', $username)->first();
        $checkPw = $user ? $user->password : '$2y$12$.'.rand(0,PHP_INT_MAX); // Equalize the time spent if no user
        return password_verify($password, $checkPw);
    }

    /////////////////////////
    // Laravel
    /////////////////////////

    /**
     * In general: configures static properties which would otherwise need to be set outside of the class.
     * This instance: automatically hash the password before sending it to the database, and automatically sets the
     * superadmin flag on the first user to register.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // TODO: Should still check if unique username in case somewhere allows the user to update it sometime.
            $model->rules['username'] = 'required|alpha_dash|min:3|max:16';
        });

        static::creating(function ($model) {
            $model->UpdatePassword($model->password);
            if (self::count() == 0) {
                $model->is_superadmin = true;
            }
        });
    }

    //////////////////////
    // Helpers
    //////////////////////
    private static function base64UrlEncode($data) { 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    private static function base64UrlDecode($data) { 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
}
