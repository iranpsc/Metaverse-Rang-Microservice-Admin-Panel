<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">جوایز</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">پیام های سلسله</a>
        </li>
        <li class="nav-item">
            <a href="#tab3" data-bs-toggle="tab" class="nav-link">مدیریت دسترسی ها</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade show active">
            @livewire('dynasty.prize')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('dynasty.dynasty-messages')
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('dynasty.permissions')
        </div>
    </div>
</div>
