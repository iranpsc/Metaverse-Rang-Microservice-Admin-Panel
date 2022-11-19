<div>
    {{-- The Master doesn't talk, he acts. --}}
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">رنج آی پی Api</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">آی پی های مجاز Api</a>
        </li>
        <li class="nav-item">
            <a href="#tab3" data-bs-toggle="tab" class="nav-link">آی پی های مجاز پنل ادمین</a>
        </li>
        <li class="nav-item">
            <a href="#tab4" data-bs-toggle="tab" class="nav-link">آی پی های بلاک شده</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade show active">
            <livewire:ip-management.api-ip-ranges>
        </div>
        <div id="tab2" class="tab-pane fade">
            <livewire:ip-management.api-allowed-ips>
        </div>
        <div id="tab3" class="tab-pane fade">
            <livewire:ip-management.admin-allowed-ips>
        </div>
        <div id="tab4" class="tab-pane fade">
        </div>
    </div>
</div>
