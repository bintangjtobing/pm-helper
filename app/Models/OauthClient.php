<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthClient extends Model
{
    protected $table = 'oauth_clients';
    protected $primaryKey = 'client_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const UPDATED_AT = null;

    protected $fillable = [
        'client_id',
        'client_name',
        'redirect_uris',
        'token_endpoint_auth_method',
        'client_uri',
        'software_id',
        'software_version',
        'last_used_at',
    ];

    protected $casts = [
        'redirect_uris' => 'array',
        'created_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function allowsRedirect(string $uri): bool
    {
        return in_array($uri, $this->redirect_uris ?? [], true);
    }
}
