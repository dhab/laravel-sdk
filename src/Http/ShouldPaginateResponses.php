<?php
namespace DreamHack\SDK\Http;

trait ShouldPaginateResponses
{
    public static function shouldPaginate() {
        return true;
    }
}
