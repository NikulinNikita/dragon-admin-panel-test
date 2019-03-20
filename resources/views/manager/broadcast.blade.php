<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/favicon.ico">
    <title>Dealer panel</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{mix('css/app.css')}}">

    <link href="https://vjs.zencdn.net/6.6.3/video-js.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/6.6.3/video.js"></script>
    <script src="https://unpkg.com/videojs-flash/dist/videojs-flash.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-contrib-hls/5.14.1/videojs-contrib-hls.js"></script>

    <style>
        * { outline: none !important; }
        body { overflow: hidden; background-color: #211508; }
        .video-js { width: 100%; height: 50vh; }

        #main { padding: 0; }
    </style>
</head>
<body>
<div id="vueApp">
    <main id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
                    <video class="video-js vjs-default-skin vjs-big-play-centered" autoplay muted data-setup='{"controls": true, "muted": true, "autoplay": true }'>
                        <source src="https://flvhttps-pull-goldendragon.speedws.com/live/roulette-front/playlist.m3u8" type="application/x-mpegURL">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
                <div class="col-6">
                    <video class="video-js vjs-default-skin vjs-big-play-centered" autoplay muted data-setup='{"controls": true, "muted": true, "autoplay": true }'>
                        <source src="https://flvhttps-pull-goldendragon.speedws.com/live/side/playlist.m3u8" type="application/x-mpegURL">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <video class="video-js vjs-default-skin vjs-big-play-centered" autoplay muted data-setup='{"controls": true, "muted": true, "autoplay": true }'>
                        <source src="https://flvhttps-pull-goldendragon.speedws.com/live/top/playlist.m3u8" type="application/x-mpegURL">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
                <div class="col-6">
                    <video class="video-js vjs-default-skin vjs-big-play-centered" autoplay muted data-setup='{"controls": true, "muted": true, "autoplay": true }'>
                        <source src="https://flvhttps-pull-goldendragon.speedws.com/live/baccarat/playlist.m3u8" type="application/x-mpegURL">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
            </div>
        </div>
    </main>

    {{--<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        if (Hls.isSupported()) {
            var video = document.getElementById('video');
            var hls = new Hls();
            hls.loadSource('https://flvhttps-pull-goldendragon.speedws.com/live/roulette-front/playlist.m3u8');
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED,function()
            {
                video.play();
            });
        }
        else if (video.canPlayType('application/vnd.apple.mpegurl'))
        {
            video.src = 'https://flvhttps-pull-goldendragon.speedws.com/live/roulette-front/playlist.m3u8';
            video.addEventListener('canplay',function()
            {
                video.play();
            });
        }
    </script>--}}
</div>
</body>
</html>
