<?php

namespace App\Models;

use App\Models\Tool\Map\Map;
use App\Models\Tool\AttackPlanner\AttackList;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use SoftDeletes, HasApiTokens, HasFactory, Notifiable;

    public $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_seen',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
    ];

    /**
     * @param $input
     */
    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }
    
    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    public function BugreportComments()
    {
        return $this->hasMany(BugreportComment::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($user){
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->save();
        });
    }

    public function followAttackList()
    {
        return $this->morphedByMany(AttackList::class, 'followable', 'follows');
    }

    public function followMap()
    {
        return $this->morphedByMany(Map::class, 'followable', 'follows');
    }

    public function followPlayer()
    {
        return $this->morphedByMany(Player::class, 'followable', 'follows');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function dsConnection()
    {
        return $this->hasMany(DsConnection::class);
    }

    public function routeNotificationForDiscord()
    {
        return $this->profile->discord_private_channel_id;
    }
}
