<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified,
            'photo' => $this->photo,
            'phone' => $this->phone,
            'active' => $this->active,
            'gender' => $this->gender,
            'role' => $this->roles->first()->name,
        ];
    }
}
