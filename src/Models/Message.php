<?php
/**
 * 用户消息模型
 */
namespace Message\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $table = 'messages'; //数据表名称


    //批量赋值白名单
    protected $fillable = ['id','user_id','from_id','msgtpl_id','subject','url','content','read'];
    //输出隐藏字段
    protected $hidden = [];
    //日期字段
    protected $dates = ['created_at','updated_at'];

    //用户消息-用户
    public function user(){
        return $this->belongsTo('App\User');
    }

    //用户消息-消息模板
    public function msgtpl(){
        return $this->belongsTo('Message\Models\Msgtpl');
    }

    //查询条件筛选
    public function scopeOptions($query,array $options=[])
    {
        //条件筛选
        collect($options['where'])->each(function($item,$key) use(&$query){
            $val = $item->exp=='like' ? '%'.preg_replace('/([_%])/','\\\$1', $item->val).'%' : $item->val;
            $item and $query->where($item->key,$item->exp,$val);
        });
        //排序
        collect($options['order'])->each(function($item,$key) use (&$query){
            $item and $query->orderBy($key,$item);
        });
        return $query;
    }


  }
