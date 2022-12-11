<div>
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">کل زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">قیمت زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">ورود به زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">زمین های فروخته شده</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab5">مبادله زمین</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab6">قیمت گذاری زمین</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab7">هزینه ورودی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab8">محدودیت قیمت گذاری</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            <livewire:lands.listing>
        </div>
        <div id="tab2" class="tab-pane fade">
            <livewire:lands.prices>
        </div>
        <div id="tab3" class="tab-pane fade">
            <livewire:lands.entries>
        </div>
        <div id="tab4" class="tab-pane fade">
            <livewire:lands.sold>
        </div>
        <div id="tab5" class="tab-pane fade">
            <livewire:lands.traded>
        </div>
        <div id="tab6" class="tab-pane fade">
            <livewire:lands.pricing>
        </div>
        <div id="tab7" class="tab-pane fade">
            <livewire:lands.entry-price>
        </div>
        <div id="tab8" class="tab-pane fade">
            <livewire:lands.feature-pricing-limits>
        </div>

    </div>
