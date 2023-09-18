<?php

namespace App\Http\Resources\Comment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comment_id' => $this->id,
            'comment' => $this->comment,
           // 'user_id' => $this->user_id,
            'post_id' => $this->post_id,
            'created_at' => Carbon::parse($this->created_at)->format('Y M d'),
            'total_comment' => $this->whenNotNull($this->current_comment)
        ];
    }
}
