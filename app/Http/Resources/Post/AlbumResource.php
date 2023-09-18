<?php

namespace App\Http\Resources\Post;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
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
            'caption' => $this->caption,
            'post_id' => $this->post_id,
            'created_at' => $this->when($request->routeIs('make.album') , Carbon::parse($this->created_at)->format('Y M d')),
            'contents' => AlbumPhotosResource::collection($this->whenLoaded('album_photos'))
        ];
    }
}
