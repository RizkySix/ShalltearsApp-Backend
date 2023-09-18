<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FindUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->foto_profile == null ? $fotoProfile = null : $fotoProfile = asset('storage/' . $this->foto_profile);
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'foto_profile' => $fotoProfile,
            'bio' => $this->bio,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'email_verified_at' => $this->when($request->routeIs('authenticated.user') , $this->email_verified_at),
            'notification_list' => $this->notification_list,
            'readed_notif' => $this->readed_notification
        ];
    }
}
