<?php
/**
 * Created by IntelliJ IDEA.
 * User: crams
 * Date: 18.08.2019
 * Time: 16:05
 */

namespace App\Models\Tool\AttackPlanner;


use App\Models\CustomModel;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class AttackList extends CustomModel
{
    use SoftDeletes;

    protected $fillable = [
        'world_id',
        'user_id',
    ];

    protected $hidden = [
        'edit_key',
        'show_key',
    ];
    

    /**
     * @return World
     */
    public function world()
    {
        return $this->belongsTo(World::class);
    }

    public function items()
    {
        return $this->hasMany(AttackListItem::class)->orderBy('send_time');
    }

    public function follows(){
        return $this->morphToMany(User::class, 'followable', 'follows');
    }

    public function nextAttack(){
        $item = $this->items->where('send_time', '>', Carbon::now())->first();
        if (!isset($item->send_time)){
            return '-';
        }
        $date = $item->send_time->locale(\App::getLocale());
        return $date->isoFormat('L LT');
    }

    public function outdatedCount(){
        return $this->items->where('send_time', '<', Carbon::now())->count();
    }

    public function attackCount(){
        return $this->items->where('send_time', '>', Carbon::now())->count();
    }
    
    public function getTitle() {
        if($this->title == null || $this->title == "") {
            return __('tool.attackPlanner.title');
        }
        return $this->title;
    }
}
