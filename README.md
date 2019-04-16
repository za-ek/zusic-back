# Zusic
PHP backend for Zusic player.

## Installation
- Create database
```
mkdir db
php index.php /install
```
- Add data directory
```
ln -s PATH_TO_MUSIC_LIB ./music
```
- Scan
```
php index.php /scan
```

## Api
<table>
<tr><th>Method</th><th>Response</th></tr>
<tr><td><pre>GET /artists</pre></td><td><pre>
{
  artists: [
    {
      id: 1,
      title: "Bob Marley",
      album_count: 11,
      track_count: 129
    },
    ...
  ]
}
</pre></td></tr>
<tr><td><pre>GET /artists/1</pre></td><td><pre>
{
  artist: {
    id: 1,
    title: "Bob Marley",
    album_count: 12,
    track_count: 129
  }
}
</pre></td></tr>
<tr><td><pre>GET /artists/1/albums</pre></td><td><pre>
{
  albums: [
    {
      id: 1,
      title: "Forever Gold",
      artist_id: 1,
      year: 1999,
      track_count: 21
    },
    ...
  ]
}
</pre></td></tr>
<tr><td><pre>GET /albums/1</pre></td><td><pre>
{
  tracks: [
    {
      id: 1,
      title: "Buffalo Soldier",
      album_id: 1,
      artist_id: 1,
      duration: 164
    },
    ...
  ]
}
</pre></td></tr>
<tr><td><pre>GET /tracks/1</pre></td><td><pre>
binary file
</pre></td></tr>
</table>