<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHInterviewImg extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_interviewer_img';
    protected $fillable = [
        'rh_interview_id',
        'face',
        'front',
        'side',
        'back'
    ];

    public function urlFace()
    {
        $urlFace = global_asset("../storage/app/public/" . tenant('studio_slug') . '/rh/model_img/' . $this->face);
        if(!isset($this->face) || is_null($this->face) || ($this->face == ''))
            $urlFace = 'images/imagen-no-disponible.png';

        return $urlFace;
    }
    public function urlFront()
    {
        $urlFront = 'storage/GB/rh/model_img/'.$this->front;
        $urlFront = global_asset("../storage/app/public/" . tenant('studio_slug') . '/rh/model_img/' . $this->front);
        if(is_null($this->front) || ($this->front == ''))
            $urlFront = 'images/imagen-no-disponible.png';

        return $urlFront;
    }
    public function urlSide()
    {
        $urlSide = 'storage/GB/rh/model_img/'.$this->side;
        $urlSide = global_asset("../storage/app/public/" . tenant('studio_slug') . '/rh/model_img/' . $this->side);
        if(is_null($this->side) || ($this->side == ''))
            $urlSide = 'images/imagen-no-disponible.png';

        return $urlSide;
    }
    public function urlBack()
    {
        $urlBack = 'storage/GB/rh/model_img/'.$this->back;
        $urlBack = global_asset("../storage/app/public/" . tenant('studio_slug') . '/rh/model_img/' . $this->back);
        if(is_null($this->back) || ($this->back == ''))
            $urlBack = 'images/imagen-no-disponible.png';
        return $urlBack;
    }

    public function ImgToInterview()
    {
        return $this->belongsTo('App\Models\HumanResources\RHInterviews','rh_interview_id');
    }
}
