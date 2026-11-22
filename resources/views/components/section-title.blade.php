@props(['title'])

<div {{ $attributes->class('section-title') }}>
    <span class="section-title__bar" aria-hidden="true"></span>
    <h2 class="section-title__text">{{ $title }}</h2>
    <span class="section-title__bar" aria-hidden="true"></span>
    {{ $slot }}
</div>
