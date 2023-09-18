<?php

namespace App\Http\Resources\Comment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecifiecPostCommentsResource extends JsonResource
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
            'id' => $this->id,
            'comment_text' => $this->comment,
            'commentator_name' => $this->user->first_name,
            'commentator_username' => $this->user->username,
            'commentator_profile' => $fotoProfile,
            'created_at' => Carbon::parse($this->created_at)->format('Y M d'),
            'total_sub_comments' => $this->total_sub_comments,
            'sub_comments_list' => SpecifiecPostSubCommentsResource::collection($this->whenNotNull($this->whenLoaded('sub_comments')))
        ];
    }
}
