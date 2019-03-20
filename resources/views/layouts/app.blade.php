<!DOCTYPE html>
<html lang="en">
@include('partials._head')
<body class="bg-primary">
    <div id="vueApp">
        @include('partials._header')

        <main id="main">
            @yield('content')
        </main>
        @include('partials._footer')
    </div>

    <script type="text/x-template" id="modal-template">
        <transition name="modal">
            <div class="vue-modal-mask">
                <div class="vue-modal-wrapper">
                    <div class="vue-modal-container">
                        <div class="vue-modal-container-content">
                            <div class="vue-modal-container-inner">
                                <slot name="content"></slot>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </script>
    <script src="{{ mix('/js/dealer-app.js') }}"></script>
</body>
</html>