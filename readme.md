Here’s a brief write-up of how I understood the domain objects, how they related to each other, and some common operations that would likely be needed.

# Domain objects

## Gallery

The Gallery is initially a 1 to 1 mapping with users; that is, each user would have exactly one gallery in the initial version of the product. A gallery is a namespace that contains all of that user’s uploaded photos and created albums. A gallery can contain zero or more photos and zero or more albums.

In later versions of the product, it may make sense to extend this functionality to allow users to have multiple galleries. This would facilitate a use case where a user wants one gallery per client, and within each gallery they can define albums for each photo shoot they perform. Initially, this would be accomplished at a single level by having each shoot be an album with no meta-grouping possible.

## Photo

This is the basic unit of interaction. Users will upload their photos. The system will scrape various details such as file size, pixel dimensions, various EXIF tags and so on. The photos will be attached to the gallery.

Each image can be added to zero or more albums.

## Album

The album is the basic logical grouping function. Albums have a name, and a collection of zero or more photos. Albums are attached to the gallery.

# Database design considerations

Galleries, photos and albums will each have their own database tables. Each row in such a table will describe one entity of such a type. To accomplish the many-to-many relationship between photos and albums, a separate cross-reference table will be used to map photos into albums using their numeric identifiers.

It is likely the application will need to be able to generate shareable links to these various entities. For example, a new user creation email might need a link to the user’s home page, or a user may wish to email an album link to a client. Using numeric identifiers is a good choice for performance of table joins, but can be problematic from an information disclosure standpoint. Therefore, each gallery, album and photo will also receive a UUID which can be used to uniquely identify it.

Common operations, such as showing a thumbnail view of each photo in an album, have corresponding database queries that would benefit from adding indices, such as on the photo_id/album_id pair in the cross-reference table.

Finally, the image data itself should be stored as files on the filesystem, outside of the webroot. The row in the photos table should include the URL or path etc where the image’s data is accessible, and an appropriate passthru program written to ensure access controls are enforced before serving the content from the file.

# DDL statements
Here’s some untested SQL that should give a pretty good idea of how these elements would be modelled:

> create table galleries (id serial, uuid varbinary(16) not null, unique key(uuid))

> create table photos (id serial, uuid varbinary(16) not null, gallery_id bigint unsigned not null, width int unsigned not null, height int unsigned not null, uploaded_at datetime not null, taken_at datetime default null, key(gallery_id), unique key(uuid), key(uploaded_at))

> create table albums (id serial, uuid varbinary(16) not null, gallery_id bigint unsigned not null, name varchar(255) not null, key(gallery_id), unique key(uuid))

> create table photos_albums_cf (id serial, photo_id bigint unsigned not null, album_id bigint unsigned not null, unique key(photo_id, album_id), key(album_id, photo_id))

A query to select all of the photos from a particular album might look like this:

> select p.* from photos p join photos_albums_cf pa on p.id=pa.photo_id where pa.album_id=123;

# Code

I've designed the API using the Swagger/OpenAPI tools and used them to generate a barebones lumen/laravel project for a proof of concept. The design of the REST API is straightforward given the simplicity of the domain described above. The YAML file that holds the API specification is included in this repository.

# Todo

- Add better error handling
- Refactor the UUID method into a single location
- Fix the minor discrepencies between the API spec document and the actual implementation
- Implement test suite
- Add schema creation via migration files
