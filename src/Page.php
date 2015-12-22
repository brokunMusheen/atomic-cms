<?php namespace BrokunMusheen\Atomic;

class Page extends ContentType
{
    protected $attributes = [
      'title' => null,
      'body' => null,
    ];

    protected static $content_type_directory = 'page';

}
