<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\User\ShallUserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   
        return [
            'uuid' => $this->uuid,
            'total_like' => $this->like,
            'liker' => $this->when($request->routeIs('specifiec.user.threads')  || $request->routeIs('all.posts') || $request->routeIs('expand.posts'),  $this->likes->pluck('user.username')->implode(',')),
            'total_comment' => $this->comments,
            'created_at' => Carbon::parse($this->created_at)->format('Y M d'),
            'archived_at' => $this->when($request->routeIs('specifiec.archived.user.albums') || $request->routeIs('archived.single.post') , Carbon::parse($this->deleted_at)->format('Y M d')),
            'thread' => ThreadResource::make($this->whenNotNull($this->whenLoaded('thread'))),
            'album' => AlbumResource::make($this->whenNotNull($this->whenLoaded('album'))),
            'user' => ShallUserResource::make($this->whenLoaded('user'))
        ];
    }
}
