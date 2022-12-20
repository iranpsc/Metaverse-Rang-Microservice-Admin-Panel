<div>
    <ul class="nav nav-tabs border">
        @can('Shop-Packages')
            <li class="nav-item">
                <a href="#tab1" class="nav-link active" data-bs-toggle="tab">بسته ها</a>
            </li>
        @endcan
        @can('Shop-Currencies')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">ارزها</a>
            </li>
        @endcan
    </ul>
    <div class="tab-content">
        @can('Shop-Packages')
            <div id="tab1" class="tab-pane fade active show">
                <livewire:variables.color-options>
            </div>
        @endcan
        @can('Shop-Currencies')
            <div id="tab2" class="tab-pane fade">
                <livewire:variables.colors-price>
            </div>
        @endcan
    </div>
</div>
