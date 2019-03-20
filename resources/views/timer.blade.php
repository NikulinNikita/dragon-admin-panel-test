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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://raw.githubusercontent.com/daneden/animate.css/master/animate.css">
    <style>
        * { outline: none !important; }
        body { overflow: hidden; background-color: #211508; }

        .clock-label {
            display: block;
            font-size: 12vw;
            color: #b6a06a; /*#ffe59a*/
            line-height: 1;
        }

        .clock {
            color: #d5d0a0; /*#fff*/
            font-family: monospace;
            font-size: 15vw;
            font-weight: bold;
            line-height: 1;
        }

        .email,
        .telephone {
            color: #b6a06a; /*#ab9562*/
            line-height: 1;
        }

        .email { font-size: 6.5vw; }
        .telephone { font-size: 10vw; }

        .email svg,
        .telephone svg {
            width: auto;
            height: 11vw;
            fill: #b6a06a; /*#ffe59a*/
        }

        .email svg {
            height: 9vw;
        }

        .telephone svg {
            height: 11vw;
        }

        .owl-carousel {
            position: relative;
            z-index: 0;
        }

        .owl-stage-outer,
        .owl-stage,
        .owl-item {
            height: 100%;
        }

        .owl-nav {
            position: absolute;
            display: flex;
            justify-content: space-between;
            width: 100%;
            height: 0;
            top: 45%;
            padding: 0 6vw;
        }

        .owl-carousel .owl-nav button.owl-prev,
        .owl-carousel .owl-nav button.owl-next {
            color: #ffe59a;
            font-size: 13vw;
            height: 13vw;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div id="vueApp">
        <main id="main" style="position: relative; padding: 0;">
            <button id="goFS" style="position: absolute; top: 0; left: 0; padding: 20px; color: #462606; border: none; background-color: transparent; z-index: 1">Go fullscreen</button>
            <div class="owl-carousel d-flex h-100 text-center">
                <div class="d-flex flex-column h-100">
                    <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1">
                        <strong class="clock-label">London</strong>
                        <div class="london clock">
                            <span class="hours">--</span><span class="colon">:</span><span class="minutes">--</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="height: 30%; background-color: #2b1c00;">
                        <div class="email">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path d="M467,61H45C20.218,61,0,81.196,0,106v300c0,24.72,20.128,45,45,45h422c24.72,0,45-20.128,45-45V106 C512,81.28,491.872,61,467,61z M460.786,91L256.954,294.833L51.359,91H460.786z M30,399.788V112.069l144.479,143.24L30,399.788z M51.213,421l144.57-144.57l50.657,50.222c5.864,5.814,15.327,5.795,21.167-0.046L317,277.213L460.787,421H51.213z M482,399.787 L338.213,256L482,112.212V399.787z"/></svg>
                            support@goldendragon.top
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column h-100 text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1">
                        <strong class="clock-label">Beijing</strong>
                        <div class="beijing clock">
                            <span class="hours">--</span><span class="colon">:</span><span class="minutes">--</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="height: 30%; background-color: #2b1c00;">
                        <div class="telephone">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path d="M192,32C85.952,32,0,103.648,0,192c0,50.944,28.736,96.128,73.312,125.376L48,368l68.96-29.536 c14.784,5.248,30.144,9.568,46.56,11.584c-2.24-9.76-3.52-19.776-3.52-30.048c0-88.224,86.112-160,192-160 c9.696,0,19.168,0.8,28.512,1.952C363.616,87.968,285.696,32,192,32z M128,152c-13.248,0-24-10.752-24-24s10.752-24,24-24 s24,10.752,24,24S141.248,152,128,152z M256,152c-13.248,0-24-10.752-24-24s10.752-24,24-24s24,10.752,24,24S269.248,152,256,152z"/><path d="M512,320c0-70.688-71.648-128-160-128s-160,57.312-160,128s71.648,128,160,128c14.528,0,28.352-2.048,41.76-4.96L480,480l-29.824-59.616C487.552,396.96,512,360.928,512,320z M304,312c-13.248,0-24-10.752-24-24s10.752-24,24-24s24,10.752,24,24S317.248,312,304,312z M400,312c-13.248,0-24-10.752-24-24s10.752-24,24-24s24,10.752,24,24S413.248,312,400,312z"/></svg>
                            +0 517 39 16 58
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column h-100">
                    <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1">
                        <strong class="clock-label">Moscow</strong>
                        <div class="moscow clock">
                            <span class="hours">--</span><span class="colon">:</span><span class="minutes">--</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="height: 30%; background-color: #2b1c00;">
                        <div class="email">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path d="M467,61H45C20.218,61,0,81.196,0,106v300c0,24.72,20.128,45,45,45h422c24.72,0,45-20.128,45-45V106 C512,81.28,491.872,61,467,61z M460.786,91L256.954,294.833L51.359,91H460.786z M30,399.788V112.069l144.479,143.24L30,399.788z M51.213,421l144.57-144.57l50.657,50.222c5.864,5.814,15.327,5.795,21.167-0.046L317,277.213L460.787,421H51.213z M482,399.787 L338.213,256L482,112.212V399.787z"/></svg>
                            support@goldendragon.top
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column h-100 text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1">
                        <strong class="clock-label">Seoul</strong>
                        <div class="seoul clock">
                            <span class="hours">--</span><span class="colon">:</span><span class="minutes">--</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="height: 30%; background-color: #2b1c00;">
                        <div class="telephone">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="90px" height="90px" viewBox="0 0 90 90" style="enable-background:new 0 0 90 90;" xml:space="preserve"><path id="WhatsApp" d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522 c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982 c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537 c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938 c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537 c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333 c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882 c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977 c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344 c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223 C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/></svg>
                            +0 517 39 16 58
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.21/moment-timezone-with-data.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script>
        $('.owl-carousel').owlCarousel({
            items: 1,
            autoplay: true,
            autoplayTimeout: 10000,
            autoplaySpeed: 1500,
            loop: true,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut'
        });

        var toggle = true,
            delay = 15;

        function update() {
            var tzLondon = moment().tz("Europe/London").add(delay, 'seconds'),
                tzHongKong = moment().tz("Asia/Hong_Kong").add(delay, 'seconds'),
                tzMoscow = moment().tz("Europe/Moscow").add(delay, 'seconds'),
                tzSeoul = moment().tz("Asia/Seoul").add(delay, 'seconds');

            $('.london .hours').html(tzLondon.format('H'));
            $('.london .minutes').html(tzLondon.format('mm'));

            $('.beijing .hours').html(tzHongKong.format('H'));
            $('.beijing .minutes').html(tzHongKong.format('mm'));

            $('.moscow .hours').html(tzMoscow.format('H'));
            $('.moscow .minutes').html(tzMoscow.format('mm'));

            $('.seoul .hours').html(tzSeoul.format('H'));
            $('.seoul .minutes').html(tzSeoul.format('mm'));

            $('.colon').css({ visibility: toggle?'visible':'hidden'});

            toggle = !toggle;
        }

        setInterval(update, 1000);

        function toggleFullScreen() {
            var doc = window.document;
            var docEl = doc.documentElement;

            var requestFullScreen = docEl.requestFullscreen || docEl.mozRequestFullScreen || docEl.webkitRequestFullScreen || docEl.msRequestFullscreen;
            var cancelFullScreen = doc.exitFullscreen || doc.mozCancelFullScreen || doc.webkitExitFullscreen || doc.msExitFullscreen;

            if(!doc.fullscreenElement && !doc.mozFullScreenElement && !doc.webkitFullscreenElement && !doc.msFullscreenElement) {
                requestFullScreen.call(docEl);
            }
            else {
                cancelFullScreen.call(doc);
            }
        }

        $('#goFS').click(function() {
            toggleFullScreen();

            var $this = $(this);

            if ($this.hasClass('off')) {
                $this.removeClass('off');
                $this.text('Go fullscreen');
            } else {
                $this.addClass('off');
                $this.text('Cancel fullscreen');
            }
        });
    </script>
</body>
</html>
