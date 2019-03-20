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


    <style>
        * { outline: none !important; }
        body { overflow: hidden; background-color: #211508; }
        iframe { height: 50vh; }

        #main { padding: 0; }
    </style>
</head>
<body>
    <div id="vueApp">
        <main id="main">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <iframe src="{{env('APP_DOMAIN')}}/ru/table/2#/roulette"
                                width="100%" frameborder="0"></iframe>
                    </div>
                    <div class="col-12">
                        <iframe src="{{env('APP_DOMAIN')}}/ru/table/1#/baccarat"
                                width="100%" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
