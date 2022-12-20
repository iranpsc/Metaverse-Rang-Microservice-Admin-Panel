<div>
    <ul class="nav nav-tabs border">
        @can('Lands-List')
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">کل زمین ها</a>
            </li>
        @endcan
        @can('Lands-Price')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">قیمت زمین ها</a>
            </li>
        @endcan
        @can('Import-Lands')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab3">ورود به زمین ها</a>
            </li>
        @endcan
        @can('Sold-Lands')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab4">زمین های فروخته شده</a>
            </li>
        @endcan
        @can('Trade-Land')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab5">مبادله زمین</a>
            </li>
        @endcan
        @can('Set-Land-Price')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab6">قیمت گذاری زمین</a>
            </li>
        @endcan
        @can('Enter-Land-Price')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab7">هزینه ورودی</a>
            </li>
        @endcan
        @can('Land-Entry-Price-Limit')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab8">محدودیت قیمت گذاری</a>
            </li>
        @endcan
    </ul>

    <div class="tab-content">
        @can('Lands-List')
            <div id="tab1" class="tab-pane fade active show">
                <livewire:lands.listing>
            </div>
        @endcan
        @can('Lands-Price')
            <div id="tab2" class="tab-pane fade">
                <livewire:lands.prices>
            </div>
        @endcan
        @can('Import-Lands')
            <div id="tab3" class="tab-pane fade">
                <livewire:lands.entries>
            </div>
        @endcan
        @can('Sold-Lands')
            <div id="tab4" class="tab-pane fade">
                <livewire:lands.sold>
            </div>
        @endcan
        @can('Trade-Land')
            <div id="tab5" class="tab-pane fade">
                <livewire:lands.traded>
            </div>
        @endcan
        @can('Set-Land-Price')
            <div id="tab6" class="tab-pane fade">
                <livewire:lands.pricing>
            </div>
        @endcan
        @can('Enter-Land-Price')
            <div id="tab7" class="tab-pane fade">
                <livewire:lands.entry-price>
            </div>
        @endcan
        @can('Land-Entry-Price-Limit')
            <div id="tab8" class="tab-pane fade">
                <livewire:lands.feature-pricing-limits>
            </div>
        @endcan
    </div>
