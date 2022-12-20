<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <ul class="nav nav-tabs border">
        @can('Dynasty-Prizes')
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">جوایز</a>
            </li>
        @endcan
        @can('Dynasty-Messages')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">پیام های سلسله</a>
            </li>
        @endcan
            <li class="nav-item">
                <a href="#tab3" data-bs-toggle="tab" class="nav-link">مدیریت دسترسی ها</a>
            </li>
    </ul>
    <div class="tab-content">
        @can('Dynasty-Prizes')
            <div id="tab1" class="tab-pane fade show active">
                @livewire('dynasty.prize')
            </div>
        @endcan
        @can('Dynasty-Messages')
            <div id="tab2" class="tab-pane fade">
                @livewire('dynasty.dynasty-messages')
            </div>
        @endcan
        <div id="tab3" class="tab-pane fade">
            @livewire('dynasty.permissions')
        </div>
    </div>
</div>
