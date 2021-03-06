<?php

/**
 * ShootProof API
 * This is documentation for a simple photo upload and management web application.
 *
 * OpenAPI spec version: 1.0.0
 * Contact: john@johnplaxco.com
 *
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen.git
 * Do not edit the class manually.
 */


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class AlbumApi extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    //TODO move this to a service, use the db to generate it, whatever. Don't copy-paste it everywhere.
    private static function uuid() {
      $data = openssl_random_pseudo_bytes(16);
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

       return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Operation addAlbum
     *
     * Creates a new album in the gallery.
     *
     *
     * @return Http response
     */
    public function addAlbum()
    {
        $input = Request::all();

        //path params validation


        //not path params validation
        if (empty($input['gallery_uuid'])) {
            throw new \InvalidArgumentException('Missing the required parameter $gallery_uuid when calling addAlbum');
        }
        if (empty($input['name'])) {
            throw new \InvalidArgumentException('Missing the required parameter $name when calling addAlbum');
        }

        $album_uuid = AlbumApi::uuid();

        $result = app('db')->insert('insert into albums (uuid, gallery_uuid, name) select ?, uuid, ? from galleries where galleries.uuid=?', [$uuid, $input['name'], $input['gallery_uuid']]);
        if (!$result) {
          return response(json_encode(['code'=>123, 'type'=>'creation error', 'message'=>'unable to create album']), 400);
        }
        
        return response(json_encode(['uuid'=>$uuid, 'name'=>$input['name']]));
    }
    /**
     * Operation albumAlbumUUIDPut
     *
     * Update an existing album's description.
     *
     * @param string $album_uuid  (required)
     *
     * @return Http response
     */
    public function albumAlbumUUIDPut($album_uuid)
    {
        $input = Request::all();

        //path params validation
        app('db')->update('update albums set name=? where uuid=?', [$input['name'], $album_uuid]);


        //not path params validation

        return response('operation successful');
    }
    /**
     * Operation deleteAlbum
     *
     * Deletes an album..
     *
     * @param string $album_uuid Album UUID to delete (required)
     *
     * @return Http response
     */
    public function deleteAlbum($album_uuid)
    {
        $input = Request::all();

        //path params validation
        app('db')->table('albums')->where('uuid', '=', $album_uuid)->delete();

        //not path params validation

        return response('operation successful');
    }
    /**
     * Operation galleryGalleryUUIDPut
     *
     * TODO: move this to the GalleryApi class. How did it even get here??
     *
     * Update an existing gallery's description.
     *
     * @param string $gallery_uuid  (required)
     *
     * @return Http response
     */
    public function galleryGalleryUUIDPut($gallery_uuid)
    {
        $input = Request::all();

        //path params validation


        //not path params validation
        app('db')->update('update galleries set name=? where uuid=?', [$input['name'], $gallery_uuid]);
        
        return response('operation successful');
    }
    /**
     * Operation photoAlbumDelete
     *
     * Remove a photo from an album.
     *
     *
     * @return Http response
     */
    public function photoAlbumDelete()
    {
        $input = Request::all();

        //path params validation


        //not path params validation
        if (!isset($input['photo_uuid'])) {
            throw new \InvalidArgumentException('Missing the required parameter $photo_uuid when calling photoAlbumDelete');
        }
        $photo_uuid = $input['photo_uuid'];

        if (!isset($input['album_uuid'])) {
            throw new \InvalidArgumentException('Missing the required parameter $album_uuid when calling photoAlbumDelete');
        }
        $album_uuid = $input['album_uuid'];

        app('db')->delete('delete from albums_photos_cf where album_id in (select id from albums where uuid=?) and photo_id in (select id from photos where uuid=?)', [$album_uuid, $photo_uuid]);


        return response('operation successful');
    }
    /**
     * Operation photoAlbumPost
     *
     * Add a photo entry to an album.
     *
     *
     * @return Http response
     */
    public function photoAlbumPost()
    {
        $input = Request::all();

        //path params validation


        //not path params validation
        if (!isset($input['photo_uuid'])) {
            throw new \InvalidArgumentException('Missing the required parameter $photo_uuid when calling photoAlbumPost');
        }
        $photo_uuid = $input['photo_uuid'];

        if (!isset($input['album_uuid'])) {
            throw new \InvalidArgumentException('Missing the required parameter $album_uuid when calling photoAlbumPost');
        }
        $album_uuid = $input['album_uuid'];

        //a cross join isn't pretty, but it works
        app('db')->insert('insert into albums_photos_cf (album_id, photo_id) select a.id, p.id from albums a join photos p where a.uuid=? and p.uuid=?', [$album_uuid, $photo_uuid]);


        return response('operation successful');
    }
}
