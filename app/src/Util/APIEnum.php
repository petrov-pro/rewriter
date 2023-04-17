<?php
namespace App\Util;

enum APIEnum: string
{

    case CACHE_LIVE = '3600';
    case CACHE_TAG = 'api-external';
    case CACHE_TAG_USER = 'user_id_';
    case GROUP_NAME = 'api-show';

}
