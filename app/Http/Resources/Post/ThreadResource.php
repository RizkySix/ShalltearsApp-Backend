<?php

namespace App\Http\Resources\Post;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'text' => $this->text,
            'post_id' => $this->post_id,
            'created_at' => $this->when($request->routeIs('make.thread') , Carbon::parse($this->created_at)->format('Y M d'))
        ];
    }
}
