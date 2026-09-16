@extends('layouts.error')

@section('title', '404')

@section('content')
<div class="error-card"><span class="error-eyebrow">Error · Not found</span><div class="error-code">404</div><h1 class="error-title">This page wandered off</h1><p class="error-sub">The link may be outdated, or the page might have been moved or deleted. Try the dashboard to get back on track.</p><div class="error-actions"><a class="btn btn--primary" href="{{ route('dashboard') }}"><svg viewbox="0 0 24 24"><path d="M3 12 12 3l9 9"></path><path d="M5 10v10h14V10"></path></svg> Back to dashboard </a><a class="btn btn--ghost" href="javascript:history.back()"><svg viewbox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg> Go back</a></div><div class="error-meta"><span><strong>STATUS</strong> 404</span> <span><strong>CODE</strong> NOT_FOUND</span> <span><strong>REF</strong> a8c1-9f23</span></div></div>
@endsection
