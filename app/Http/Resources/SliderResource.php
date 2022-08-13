<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{

    public $status;
    public $message;

    public function __construct($status, $message, $resource)
    {
        parent::__construct($resource);
        $this->status  = $status;
        $this->message = $message;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'success' => $this->status,
            'message' => $this->message,
            'data'    => $this->resource
        ];
    }

}
