<div>
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">غلط املایی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">خطای FPS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">بی احترامی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">خطا در نمایش</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab5">خطا در کد نویسی</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade show active">
            <livewire:reports.spelling-error :reports="$reports">
        </div>
        <div id="tab2" class="tab-pane fade">
            <livewire:reports.fps-error :reports="$reports">
        </div>
        <div id="tab3" class="tab-pane fade">
            <livewire:reports.disrespect :reports="$reports">
        </div>
        <div id="tab4" class="tab-pane fade">
            <livewire:reports.display-error :reports="$reports">
        </div>
        <div id="tab5" class="tab-pane fade">
            <livewire:reports.coding-error :reports="$reports">
        </div>
    </div>
</div>
