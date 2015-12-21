<?php

use Atomic\Content\Page;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use org\bovigo\vfs\VfsStream;
use org\bovigo\vfs\VfsStreamWrapper;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class ContentTest extends TestCase
{
    /** @test */
    public function content_loads_from_a_flat_file()
    {
        $page_content = [
            'title' => 'Test Page',
            'body'  => 'Lorem ipsum',
        ];

        $dumper = new Dumper;
        $page_yaml = $dumper->dump( $page_content );

        $this->create_virtual_filesystem( [
                                              'page' => [
                                                  '1.yml' => $page_yaml,
                                              ],
                                          ] );

        $page = Page::load( 1 );

        $this->assertEquals( $page->title, 'Test Page' );
        $this->assertEquals( $page->body, 'Lorem ipsum' );
    }


    /** @test */
    public function content_saves_to_a_flat_file()
    {
        $this->create_virtual_filesystem();

        $page = new Page;

        $page->title = 'Test save';
        $page->body = 'Test save body.';

        $page_id = $page->save();


        $page_content = file_get_contents( config( 'content.root' ) . '/page/' . $page_id . '.yml' );

        $page_content_data = Yaml::parse( $page_content );

        $this->assertEquals( $page_content_data[ 'title' ], $page->title );
        $this->assertEquals( $page_content_data[ 'body' ], $page->body );
    }


    /** @test */
    public function all_content_data_is_retrievable()
    {
        $this->create_virtual_filesystem( [
                                              'page' => [
                                                  '1.yml' => 'Test doc 1',
                                                  '2.yml' => 'Test doc 2',
                                                  '3.yml' => 'Test doc 3',
                                              ],
                                          ] );

        $page = new Page;

        $results = $page->all();

        $this->assertEquals( [ 1, 2, 3 ], $results );
    }


    /** @test */
    public function content_is_mass_fillable()
    {
        $page = new Page;

        $page_content = [
            'title' => 'Fillable Title',
            'body'  => 'Fillable body.',
        ];
        $page->fill( $page_content );

        $this->assertEquals( $page_content[ 'title' ], $page->title );
        $this->assertEquals( $page_content[ 'body' ], $page->body );
    }


    /** @test */
    public function content_edits_are_saved_in_place()
    {
        $page_content = [
            'title' => 'Test Page',
            'body'  => 'Lorem ipsum',
        ];

        $dumper = new Dumper;
        $page_yaml = $dumper->dump( $page_content );

        $this->create_virtual_filesystem( [
                                              'page' => [
                                                  '1.yml' => $page_yaml,
                                              ],
                                          ] );

        $page = Page::load(1);

        $page->title = 'Changed Title';
        $page->body = 'Changed Body';
        $saved_page_id = $page->save();

        $this->assertEquals(1, $saved_page_id);
    }

    /** @test */
    public function content_is_deletable()
    {
        $this->create_virtual_filesystem( [
                                              'page' => [
                                                  '1.yml' => 'Deletable content',
                                              ],
                                          ] );

        $this->assertTrue(Page::delete(1));
        $this->assertFalse(file_exists(config('content.root') . '/1.yml'));
    }

    protected function create_virtual_filesystem( $structure = [ ] )
    {
        VfsStream::setup( 'root_dir', null, $structure );
        config( [ 'content.root' => VfsStream::url( 'root_dir' ) ] );
    }
}
