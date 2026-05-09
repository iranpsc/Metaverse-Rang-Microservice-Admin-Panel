@props([
    'type' => 'success',
    'message' => 'پیامی برای نمایش وجود ندارد.',
])

<div class="alert alert-{{ $type }} fill alert-dismissable fade show">
    <span class="close" data-bs-dismiss="alert">&times;</span>
    {{ $message }}
</div>
