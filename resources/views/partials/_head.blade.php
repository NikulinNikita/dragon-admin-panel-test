<head>
	<link rel="icon" href="/favicon.ico">
	<title>{{ isset($metatitle) && $metatitle ? $metatitle : (config('admin.title') ? config('admin.title'): 'Dealer panel') }}</title>
    <meta name="description" content="{{ isset($metadesc) ? $metadesc : null }}">
    <meta name="keywords" content="{{ isset($metakeyw) ? $metakeyw : null }}">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="stylesheet" href="{{mix('css/app.css')}}">
</head>