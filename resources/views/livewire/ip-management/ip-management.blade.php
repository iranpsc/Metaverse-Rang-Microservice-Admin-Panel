<div>
    {{-- The Master doesn't talk, he acts. --}}
    <ul class="nav nav-tabs border">
        @can('Define-Range-IP')
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">رنج آی پی Api</a>
        </li>
        @endcan
        @can('Allowed-API-IPs')
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">آی پی های مجاز Api</a>
        </li>
        @endcan
        @can('Allowed-Admin-IPs')
        <li class="nav-item">
            <a href="#tab3" data-bs-toggle="tab" class="nav-link">آی پی های مجاز پنل ادمین</a>
        </li>
        @endcan
        @can('Blocked-IPs')
        <li class="nav-item">
            <a href="#tab4" data-bs-toggle="tab" class="nav-link">آی پی های بلاک شده</a>
        </li>
        @endcan
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade show active">
            <livewire:ip-management.api-ip-ranges>
        </div>
        @can('Allowed-API-IPs')
        <div id="tab2" class="tab-pane fade">
            <livewire:ip-management.api-allowed-ips>
        </div>
        @endcan
        @can('Allowed-Admin-IPs')
        <div id="tab3" class="tab-pane fade">
            <livewire:ip-management.admin-allowed-ips>
        </div>
        @endcan
        <div id="tab4" class="tab-pane fade">
        </div>
    </div>
</div>
