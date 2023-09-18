<?php

namespace App\Http\Resources\Like;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->user->foto_profile == null ? $fotoProfile = null : $fotoProfile = asset('storage/' . $this->user->foto_profile);
        return [
            'first_name' => $this->user->first_name,
            'username' => $this->user->username,
            'foto_profile' => $fotoProfile
        ];
    }
}
