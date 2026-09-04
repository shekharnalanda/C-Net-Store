@extends('layouts.storefront')
@section('title', $page->meta_title ?: $page->title.' | C-Net Store')
@section('meta_description', $page->meta_description ?: $page->title)
@section('content')
<section class="section container">
    <article style="max-width:900px;margin:0 auto;background:#fff;border:1px solid #e7ebf0;border-radius:18px;padding:clamp(24px,5vw,56px);box-shadow:0 12px 36px rgba(20,42,74,.08)">
        <span class="eyebrow">C-Net Store information</span>
        <h1 style="margin:10px 0 22px">{{ $page->title }}</h1>
        <div style="font-size:16px;line-height:1.85;color:#3d4653">{!! nl2br(e($page->content)) !!}</div>
        <p style="margin-top:28px;color:#6b7280;font-size:13px">Last updated: {{ $page->updated_at?->format('d F Y') }}</p>
    </article>
</section>
@endsection
