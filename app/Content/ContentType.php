<?php namespace Atomic\Content;

use Symfony\Component\Yaml\Yaml;

abstract class ContentType
{
    protected $id = null;
    protected $attributes = [];

    public function __construct($content_id = null)
    {
        $this->id = $content_id;
    }

    public function fill(array $values)
    {
        foreach($values as $key => $value)
        {
            if(array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public static function all()
    {
        $contents = scandir( static::get_content_directory() );

        $ids = [ ];

        foreach( $contents as $content )
        {
            $matches = [ ];

            if( preg_match( "/(\d)\.yml$/i", $content, $matches ) )
            {
                $ids[] = $matches[ 1 ];
            }
        }

        return $ids;
    }

    public static function delete($id)
    {
        $filename = static::get_content_directory() . '/' . $id . '.yml';

        if(file_exists( $filename ))
        {
            return unlink($filename);
        }

        return true;
    }

    public static function load( $id )
    {
        $page_content_file = static::get_content_directory() . '/' . $id . '.yml';

        $page_content = file_get_contents( $page_content_file );

        $page_content_data = Yaml::parse( $page_content );

        $page = new static($id);

        $page->fill($page_content_data);

        return $page;
    }


    public function save()
    {
        $pages_dir = static::get_content_directory();

        if( ! file_exists( $pages_dir ) )
        {
            if( ! mkdir( $pages_dir ) )
            {
                throw new \Exception( 'Error creating content directory: ' . $pages_dir );
            }
        }

        if($this->id)
        {
            $id = $this->id;
        }
        else
        {
            // Get next available Page ID
            $ids = static::all();
            if( count( $ids ) )
            {
                $id = max( $ids ) + 1;
            }
            else
            {
                $id = 1;
            }
        }

        $yaml_dumper = Yaml::dump($this->attributes);

        file_put_contents($pages_dir . '/' . $id . '.yml', $yaml_dumper);

        return $id;
    }

    public function __get( $name )
    {
        if($name == 'id')
        {
            return $this->id;
        }
        elseif(array_key_exists($name, $this->attributes))
        {
            return $this->attributes[$name];
        }
        else
        {
            return null;
        }
    }

    public function __set( $name, $value )
    {
        if(array_key_exists($name, $this->attributes))
        {
            $this->attributes[$name] = $value;
        }
    }

    protected static function get_content_directory()
    {
        return sprintf('%s/%s',
                       config('content.root'),
                       static::$content_type_directory);
    }
}