<div>
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a href="#tab1" class="nav-link active" data-bs-toggle="tab">مسئولیت ها و دسترسی های کارمندان</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">مسئولیت ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">دسترسی ها</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            <livewire:access-management.employee-role-permission>
        </div>
        <div id="tab2" class="tab-pane fade">
            <livewire:access-management.roles>
        </div>
        <div id="tab3" class="tab-pane fade">
            <livewire:access-management.permissions>
        </div>
    </div>
</div>
